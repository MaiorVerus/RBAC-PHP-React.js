// register.jsx
import { useState } from "react";
import { useNavigate } from "react-router-dom"; // ← added
import css from "./register.module.css";
import axios from "axios";
// import { useAuth } from "../../context/AuthContext"; // ← path fixed

export default function Register() {
    // const { login } = useAuth();
    const navigate = useNavigate(); // ← added
    const [action, setAction] = useState("login");
    const [err, setErr] = useState(null);
    const [formData, setFormData] = useState({
        username: '',
        email: '',
        password: ''
    });

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData(prev => ({ ...prev, [name]: value }));
    };

    async function handleSubmit(e) {
        e.preventDefault();
        setErr(null);

        const url = action === "login"
            ? "http://localhost/uni-mngmt-sys/api/public/auth/login"
            : "http://localhost/uni-mngmt-sys/api/public/auth/signup";

        try {
            const response = await axios.post(url, formData);

            if (action === "login") {
                const { token, user } = response.data;
                // login(token, user); // ← stores token + user in context & localStorage

                // Role-based redirect
                const routes = {
                    student: '/student/dashboard',
                    lecturer: '/lecturer/dashboard',
                    hod: '/hod/dashboard',
                    admin: '/admin/dashboard',
                };

                navigate(routes[user.role] ?? '/dashboard');

            } else {
                setAction("login");
                alert("Signup successful! Please log in.");
            }

        } catch (error) {
            const message = error.response?.data?.error
                ?? error.message
                ?? "Something went wrong. Please try again.";
            setErr(message);
        }
    }

}