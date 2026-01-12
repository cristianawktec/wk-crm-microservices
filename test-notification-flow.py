#!/usr/bin/env python3
import requests
import json

# Get test token
response = requests.get('http://localhost:8000/api/auth/test-customer')
data = response.json()
token = data.get('token')
user_id = data.get('user', {}).get('id')

print(f"Token: {token[:40]}...")
print(f"User ID: {user_id}")

# Create new opportunity
headers = {'Authorization': f'Bearer {token}', 'Content-Type': 'application/json'}
opp_data = {
    'title': 'Test Opportunity - Detail Page',
    'value': 50000,
    'status': 'open',
    'probability': 60,
    'notes': 'Testing notification detail page navigation'
}

response = requests.post('http://localhost:8000/api/customer-opportunities', json=opp_data, headers=headers)
print(f"\nCreate opportunity response: {response.status_code}")
if response.status_code in [200, 201]:
    opp = response.json().get('data', {})
    print(f"Opportunity created: {opp.get('id')} - {opp.get('title')}")
    
    # Now get the opportunity detail
    response = requests.get(f'http://localhost:8000/api/customer-opportunities/{opp.get("id")}', headers=headers)
    print(f"\nGet opportunity detail response: {response.status_code}")
    if response.status_code == 200:
        detail = response.json().get('data', {})
        print(f"Detail loaded: {detail.get('title')}")
        print(f"Value: {detail.get('value')}")
        print(f"Status: {detail.get('status')}")
else:
    print(f"Error: {response.text}")
