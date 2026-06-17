import os
from pathlib import Path
from dotenv import load_dotenv

SCRIPT_DIR = Path(__file__).parent.resolve()
DOTENV_PATH = SCRIPT_DIR / ".env"
load_dotenv(dotenv_path=DOTENV_PATH)

# ==========================================
# INSTELLINGEN - WORDEN INGELEZEN UIT .env
# ==========================================
WP_URL = os.getenv("WORDPRESS_URL")
WP_USERNAME = os.getenv("WORDPRESS_USERNAME")
WP_APP_PASSWORD = os.getenv("WORDPRESS_APP_PASSWORD")
# ==========================================

def clean_database():
    auth = (WP_USERNAME, WP_APP_PASSWORD)
    url = f"{WP_URL}/wp-json/wp/v2/kleurplaten?per_page=100"
    
    print("📡 Verbinden met WordPress en zoeken naar kleurplaten...")
    response = requests.get(url, auth=auth)
    
    if response.status_code != 200:
        print(f"❌ Fout bij verbinden: {response.status_code} - {response.text}")
        return

    posts = response.json()
    if not posts:
        print("✅ De database is al helemaal leeg. Geen kleurplaten gevonden.")
        return

    print(f"🗑️ Er zijn {len(posts)} kleurplaten gevonden. Wissen start nu...")

    for post in posts:
        post_id = post['id']
        media_id = post.get('featured_media')
        title = post['title']['rendered']
        
        # Stap 1: Verwijder de bijbehorende afbeelding uit de media bibliotheek
        if media_id and media_id > 0:
            media_url = f"{WP_URL}/wp-json/wp/v2/media/{media_id}?force=true"
            media_resp = requests.delete(media_url, auth=auth)
            if media_resp.status_code == 200:
                print(f"   🖼️ Afbeelding {media_id} permanent gewist.")
            else:
                print(f"   ⚠️ Kon afbeelding {media_id} niet wissen.")

        # Stap 2: Verwijder de kleurplaat post zelf permanent (force=true slaat de prullenbak over)
        delete_url = f"{WP_URL}/wp-json/wp/v2/kleurplaten/{post_id}?force=true"
        del_resp = requests.delete(delete_url, auth=auth)
        
        if del_resp.status_code == 200:
            print(f"   📄 Post '{title}' (ID: {post_id}) succesvol verwijderd.")
        else:
            print(f"   ❌ Fout bij wissen post '{title}': {del_resp.text}")

    print("\n✅ Schoonmaak voltooid! Je hebt weer een compleet lege database.")

if __name__ == "__main__":
    if not all([WP_URL, WP_USERNAME, WP_APP_PASSWORD]):
        print("Error: Set WORDPRESS_URL, WORDPRESS_USERNAME and WORDPRESS_APP_PASSWORD environment variables")
        exit(1)
    clean_database()