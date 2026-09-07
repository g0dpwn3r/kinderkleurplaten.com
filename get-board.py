import os
import sys
from pathlib import Path

# Laad environment variables uit .env
try:
    from dotenv import load_dotenv
    SCRIPT_DIR = Path(__file__).parent.resolve()
    load_dotenv(dotenv_path=SCRIPT_DIR / ".env")
except ImportError:
    pass

import requests

PINTEREST_ACCESS_TOKEN = os.getenv("PINTEREST_ACCESS_TOKEN", "")
if not PINTEREST_ACCESS_TOKEN:
    print("Fout: PINTEREST_ACCESS_TOKEN niet gevonden in .env", file=sys.stderr)
    sys.exit(1)

headers = {
    "Authorization": f"Bearer {PINTEREST_ACCESS_TOKEN}"
}

try:
    response = requests.get("https://api.pinterest.com/v5/boards", headers=headers, timeout=10)
    
    if response.status_code == 200:
        data = response.json()
        boards = data.get("items", [])
        
        if not boards:
            print("Geen borden gevonden. Heb je al een bord aangemaakt op Pinterest?")
        else:
            print("\n=== JOUW PINTEREST BORDEN ===")
            for board in boards:
                print(f"Naam: {board.get('name')} -> ID: {board.get('id')}")
            print("=============================\n")
    else:
        print(f"Foutcode: {response.status_code}")
        print(f"Details: {response.text}")

except Exception as e:
    print(f"Er ging iets mis met de verbinding: {e}")
