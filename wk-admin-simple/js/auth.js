// Simple auth helpers for Admin Simple
function getToken() {
  return localStorage.getItem('wk_token');
}

async function requireAuth() {
  const token = getToken();
  if (!token) {
    window.location.href = 'login.html?next=' + encodeURIComponent(window.location.pathname.split('/').pop());
    return false;
  }
  // Optionally verify token with backend
  try {
    const res = await fetch((window.WK_API_BASE || (location.hostname.startsWith('api.') ? location.origin + '/api' : (location.hostname.includes('consultoriawk.com') ? 'https://api.consultoriawk.com/api' : 'http://localhost:8000/api'))) + '/auth/me', {
      headers: { 'Authorization': 'Bearer ' + token, 'Accept':'application/json' }
    });
    if (res.status === 401) {
      logout();
      return false;
    }
  } catch (_) {}
  return true;
}

function logout() {
  localStorage.removeItem('wk_token');
  localStorage.removeItem('wk_user');
  window.location.href = 'login.html';
}
