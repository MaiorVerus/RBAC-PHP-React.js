import { Navigate } from 'react-router-dom'
import { useAuth } from '../context/AuthContext'

export default function PrivateRoute({ children, allowedRoles }) {
  const { user, loading } = useAuth()

  if (loading) {
    return <div>Loading...</div>
  }

  if (!user) {
    return <Navigate to="/register" replace />
  }

  if (allowedRoles && !allowedRoles.includes(user.role)) {
    return <div>You do not have permission to view this page.</div>
  }

  return children
}
