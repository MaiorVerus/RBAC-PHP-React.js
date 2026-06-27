import axios from 'axios'

const API_BASE = 'http://localhost/CRUD RBAC/api/auth'

export function login({ email, password }) {
  return axios.post(`${API_BASE}/login.php`, { email, password })
}

export function signup({ username, email, password }) {
  return axios.post(`${API_BASE}/signup.php`, { username, email, password })
}

export function fetchMe(token) {
  return axios.get(`${API_BASE}/me.php`, {
    headers: { Authorization: `Bearer ${token}` },
  })
}
