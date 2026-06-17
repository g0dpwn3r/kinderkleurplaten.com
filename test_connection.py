import requests 

try: 
    test = requests.get('https://router.huggingface.co', timeout=5)
    print("Het werkt! Status:", test.status_code) 
except Exception as e: 
    print("TOTAAL GEEN INTERNET. Fout:", e)