#!/usr/bin/env python3
import urllib.request
import json
import sys

# Data to send
data = {
    "title": "Venda Nova Importante",
    "value": 50000,
    "probability": 75,
    "status": "Negotiation",
    "client_name": "Tech Solutions Inc"
}

# Convert to JSON
json_data = json.dumps(data).encode('utf-8')

print("Sending request to FastAPI service...")
print(f"Payload: {json.dumps(data, indent=2)}")
print()

# Make request
try:
    req = urllib.request.Request(
        'http://localhost:8001/ai/opportunity-insights',
        data=json_data,
        headers={'Content-Type': 'application/json'}
    )
    
    with urllib.request.urlopen(req, timeout=30) as response:
        response_data = response.read().decode('utf-8')
        result = json.loads(response_data)
        print("✅ SUCCESS - Response from AI Service:")
        print(json.dumps(result, indent=2, ensure_ascii=False))
        
except urllib.error.HTTPError as e:
    print(f"❌ HTTP Error: {e.code}")
    error_body = e.read().decode('utf-8')
    try:
        error_json = json.loads(error_body)
        print(json.dumps(error_json, indent=2))
    except:
        print(error_body)
        
except urllib.error.URLError as e:
    print(f"❌ Connection Error: {e.reason}")
    
except Exception as e:
    print(f"❌ Error: {e}")
    sys.exit(1)
