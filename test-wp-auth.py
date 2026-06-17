#!/usr/bin/env python3
"""
WordPress Authentication Diagnostics Script

Tests WordPress REST API authentication using Application Passwords.
Provides detailed diagnostic output to help troubleshoot 401 errors.
"""

import os
import sys
import base64
import json
from pathlib import Path
from typing import Optional, Dict, Any

import requests
from dotenv import load_dotenv

SCRIPT_DIR = Path(__file__).parent.resolve()
DOTENV_PATH = SCRIPT_DIR / ".env"
load_dotenv(dotenv_path=DOTENV_PATH)

# ============================================================
# CONFIGURATION
# ============================================================

WORDPRESS_URL = os.getenv("WORDPRESS_URL", "http://94.130.141.198:8081")
WORDPRESS_USERNAME = os.getenv("WORDPRESS_USERNAME", "admin")
WORDPRESS_APP_PASSWORD = os.getenv("WORDPRESS_APP_PASSWORD", "Ds9X0i8aigdNhssJB6OKZgMz")

# Request timeout
HTTP_TIMEOUT = 15


# ============================================================
# UTILITY FUNCTIONS
# ============================================================

def get_basic_auth_header() -> Dict[str, str]:
    """
    Generate the Basic Auth header for WordPress REST API.
    """
    if not WORDPRESS_USERNAME or not WORDPRESS_APP_PASSWORD:
        return {}
    credentials = f"{WORDPRESS_USERNAME}:{WORDPRESS_APP_PASSWORD}"
    encoded = base64.b64encode(credentials.encode("utf-8")).decode("utf-8")
    return {"Authorization": f"Basic {encoded}"}


def make_request(url: str, headers: Dict[str, str]) -> Optional[requests.Response]:
    """
    Make a GET request and return the response.
    """
    try:
        response = requests.get(url, headers=headers, timeout=HTTP_TIMEOUT)
        return response
    except requests.exceptions.Timeout:
        print(f"[ERROR] Request timed out: {url}")
        return None
    except requests.exceptions.ConnectionError:
        print(f"[ERROR] Connection error: {url}")
        return None
    except requests.exceptions.RequestException as e:
        print(f"[ERROR] Request failed: {e}")
        return None


# ============================================================
# DIAGNOSTIC CHECKS
# ============================================================

def check_env_vars() -> bool:
    """
    Verify that required environment variables are loaded and not empty.
    """
    print("=" * 60)
    print("ENVIRONMENT VARIABLE CHECK")
    print("=" * 60)

    issues = []

    if not WORDPRESS_URL:
        issues.append("WORDPRESS_URL is not set or empty")
    else:
        print(f"  WORDPRESS_URL     : {WORDPRESS_URL}")

    if not WORDPRESS_USERNAME:
        issues.append("WORDPRESS_USERNAME is not set or empty")
    else:
        print(f"  WORDPRESS_USERNAME: {WORDPRESS_USERNAME}")

    if not WORDPRESS_APP_PASSWORD:
        issues.append("WORDPRESS_APP_PASSWORD is not set or empty")
    else:
        # Mask the password for security
        masked = WORDPRESS_APP_PASSWORD[:4] + "****" + WORDPRESS_APP_PASSWORD[-4:] if len(WORDPRESS_APP_PASSWORD) > 8 else "****"
        print(f"  WORDPRESS_APP_PASSWORD: {masked} (length: {len(WORDPRESS_APP_PASSWORD)})")

    if issues:
        print("\n[FAIL] Missing environment variables:")
        for issue in issues:
            print(f"  - {issue}")
        return False

    print("\n[PASS] All environment variables are loaded.")
    return True


def check_basic_auth_header() -> bool:
    """
    Verify the Basic Auth header is correctly constructed.
    """
    print("\n" + "=" * 60)
    print("BASIC AUTH HEADER CHECK")
    print("=" * 60)

    if not WORDPRESS_USERNAME or not WORDPRESS_APP_PASSWORD:
        print("[FAIL] Cannot construct header: missing credentials")
        return False

    header = get_basic_auth_header()
    auth_value = header.get("Authorization", "")

    if not auth_value.startswith("Basic "):
        print("[FAIL] Authorization header does not start with 'Basic '")
        return False

    encoded_part = auth_value.replace("Basic ", "")
    print(f"  Base64 encoded part: {encoded_part[:20]}... (length: {len(encoded_part)})")
    print(f"  Full header: Authorization: Basic {encoded_part[:10]}...")

    # Verify we can decode it back
    try:
        decoded = base64.b64decode(encoded_part).decode("utf-8")
        print(f"  Decoded credentials : {WORDPRESS_USERNAME}:****")
        print(f"  Username matches    : {decoded.startswith(WORDPRESS_USERNAME)}")
    except Exception as e:
        print(f"[FAIL] Could not decode base64: {e}")
        return False

    print("\n[PASS] Basic Auth header is correctly constructed.")
    return True


def test_wp_authentication() -> None:
    """
    Test WordPress authentication by hitting the /users/me endpoint.
    """
    print("\n" + "=" * 60)
    print("WORDPRESS AUTHENTICATION TEST")
    print("=" * 60)

    if not WORDPRESS_APP_PASSWORD:
        print("[ERROR] Cannot proceed: WORDPRESS_APP_PASSWORD is empty.")
        return

    endpoint = f"{WORDPRESS_URL.rstrip('/')}/wp-json/wp/v2/users/me"
    headers = get_basic_auth_header()

    print(f"  Endpoint: {endpoint}")
    print(f"  Method  : GET")
    print(f"  Headers : Authorization: Basic ****")
    print()

    response = make_request(endpoint, headers)

    if response is None:
        print("[ERROR] No response received from WordPress.")
        return

    status_code = response.status_code
    print(f"  HTTP Status Code: {status_code}")

    try:
        response_body = response.json()
        response_text = json.dumps(response_body, indent=2)
    except ValueError:
        response_text = response.text

    print(f"  Response Body:")
    print("-" * 60)
    print(response_text[:2000])  # Limit output to prevent flooding
    print("-" * 60)

    # Analyze result
    print("\n" + "=" * 60)
    if status_code == 200:
        print("SUCCESS! Authentication worked.")
        print("=" * 60)

        roles = response_body.get("roles", [])
        name = response_body.get("name", "Unknown")
        username = response_body.get("slug", "Unknown")

        print(f"  Logged in as: {name} (@{username})")
        print(f"  Roles       : {', '.join(roles) if roles else 'No roles found'}")

        if "administrator" in roles or "editor" in roles or "author" in roles:
            print("  Permissions : User has permission to upload media.")
        else:
            print("  WARNING     : User may NOT have permission to upload media.")
            print("                Required roles: administrator, editor, or author.")

    elif status_code == 401:
        print("ERROR: Authentication failed.")
        print("=" * 60)
        print("\nPossible causes:")
        print("  1. The application password is incorrect or has been revoked.")
        print("  2. The username is wrong.")
        print("  3. The .htaccess rules are interfering with the Authorization header.")
        print("  4. The WordPress Application Passwords plugin/feature is disabled.")
        print("\nNext steps:")
        print("  1. Generate a new application password in WP Admin > Users > Profile > Application Passwords")
        print("  2. Update WORDPRESS_APP_PASSWORD and try again.")

    elif status_code == 403:
        print("ERROR: Authentication succeeded but permission denied.")
        print("=" * 60)
        print("  The credentials are correct, but this user cannot access this endpoint.")
        print("  Check user roles and capabilities.")

    else:
        print(f"ERROR: Unexpected status code {status_code}")
        print("=" * 60)


# ============================================================
# MAIN
# ============================================================

def main():
    """Run all diagnostic checks."""
    print()
    print("#" * 60)
    print("# WordPress Auth Diagnostics")
    print("#" * 60)

    # Check environment variables first
    env_ok = check_env_vars()
    if not env_ok:
        print("\n[ABORT] Fix environment variables before continuing.")
        return

    # Check Basic Auth header construction
    auth_ok = check_basic_auth_header()
    if not auth_ok:
        print("\n[ABORT] Fix Basic Auth header before continuing.")
        return

    # Test actual WordPress authentication
    test_wp_authentication()


if __name__ == "__main__":
    main()
