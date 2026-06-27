import { BrowserRouter, Route, Routes } from "react-router-dom"

import { AuthProvider } from "./context/AuthContext"
import PrivateRoute from "./components/PrivateRoute"
import Register from "./pages/register/register"
import LandingPage from "./pages/landing/landing-pg"
import UserDashboard from "./pages/user/dashboard/dashboard"
import AdminDashboard from "./pages/admin/dashboard/dashboard"

function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <Routes>
          <Route path="/" element={<LandingPage />} />
          <Route path="/register" element={<Register />} />
          <Route path="/user" element={<PrivateRoute><UserDashboard /></PrivateRoute>} />
          <Route path="/admin" element={<PrivateRoute allowedRoles={["admin"]}><AdminDashboard /></PrivateRoute>} />
        </Routes>
      </BrowserRouter>
    </AuthProvider>
  )
}

export default App;
