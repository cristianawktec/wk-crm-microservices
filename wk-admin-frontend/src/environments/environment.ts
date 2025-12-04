export const environment = {
  production: false,
  // Use a relative `/api` in development so the dev-server can proxy requests
  // to the backend and avoid CORS issues.
  // During debugging we can point directly to the backend to rule out proxy issues
  // Use relative `/api` so the dev-server proxy forwards requests to the backend
  apiUrl: '/api'
};
