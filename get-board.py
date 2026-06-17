import requests

# Plak hier tijdelijk de lange token-code die je van Pinterest hebt gekregen
TOKEN = "pina_AMAWAIYYADG4QBYAGBAGODYRSZD5LHQBQBIQCG2WAUB45AHLYJWUTCHWO4IO7QYQ5AHSQ76RZOKQ4ZCPTYOAQHNL7AXRP4AA"

headers = {
    "Authorization": f"Bearer {TOKEN}"
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