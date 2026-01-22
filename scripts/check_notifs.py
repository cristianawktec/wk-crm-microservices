import requests, sys, json

login = requests.post('https://api.consultoriawk.com/api/auth/login', json={'email':'admin@consultoriawk.com','password':'Admin@2025'})
print('login_status', login.status_code)
print('login_body', login.text)
try:
    data = login.json()
except Exception as e:
    print('login_json_error', e)
    sys.exit(1)

token = data.get('token')
if not token:
    print('no token')
    sys.exit(0)

notifications = requests.get('https://api.consultoriawk.com/api/notifications', headers={'Authorization': f'Bearer {token}'})
print('notif_status', notifications.status_code)
print('notif_body', notifications.text)
