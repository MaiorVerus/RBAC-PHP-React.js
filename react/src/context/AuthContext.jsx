import { createContext, useContext, useEffect, useState } from 'react'
import { fetchMe } from '../services/auth'

const AuthContext = createContext(null)

export function AuthProvider({ children }) {
  const [user, setUser] = useState(() => {
    const stored = localStorage.getItem('rbac_user')
    return stored ? JSON.parse(stored) : null
  })
  const [token, setToken] = useState(() => localStorage.getItem('rbac_token'))
  const [loading, setLoading] = useState(Boolean(token))

  useEffect(() => {
    if (!token) {
      setLoading(false)
      return
    }

    fetchMe(token)
      .then((response) => {
        setUser(response.data.user)
      })
      .catch(() => {
        setToken(null)
        setUser(null)
        localStorage.removeItem('rbac_token')
        localStorage.removeItem('rbac_user')
      })
      .finally(() => setLoading(false))
  }, [token])

  return (
    <AuthContext.Provider value={{ user, setUser, token, setToken, loading }}>
      {children}
    </AuthContext.Provider>
  )
}

export function useAuth() {
  const context = useContext(AuthContext)
  if (!context) {
    throw new Error('useAuth must be used inside AuthProvider')
  }
  return context
}
