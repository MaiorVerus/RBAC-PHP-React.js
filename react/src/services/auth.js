import axios from 'axios'

const API_BASE = 'http://localhost/CRUD-RBAC/api'

export function login({ email, password }) {
  return axios.post(`${API_BASE}/auth/login`, { email, password })
}

export function signup({ username, name, email, password }) {
  return axios.post(`${API_BASE}/auth/register`, {
    name: name ?? username,
    email,
    password,
  })
}

export function fetchMe(token) {
  return axios.get(`${API_BASE}/auth/me`, {
    headers: { Authorization: `Bearer ${token}` },
  })
}
