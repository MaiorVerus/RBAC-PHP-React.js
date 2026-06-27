import { useState } from "react"
import { useNavigate } from "react-router-dom"
import { useAuth } from "../../context/AuthContext"
import css from "./register.module.css"
import { login, signup } from "../../services/auth"

export default function Register() {
    const [action, setAction] = useState("login")
    const [err, setErr] = useState(null)
    const [formData, setFormData] = useState({
        username: '',
        email: '',
        password: ''
    })
    const navigate = useNavigate()
    const { setUser, setToken } = useAuth()

    const handleChange = (e) => {
        const { name, value } = e.target
        setFormData((prevData) => ({
            ...prevData,
            [name]: value
        }))
    }

    async function handleSubmit(e) {
        e.preventDefault()
        setErr(null)

        try {
            const response = action === "login"
                ? await login(formData)
                : await signup(formData)

            const { token, user } = response.data
            setToken(token)
            setUser(user)
            localStorage.setItem('rbac_token', token)
            localStorage.setItem('rbac_user', JSON.stringify(user))

            if (user.role === 'admin') {
                navigate('/admin')
            } else {
                navigate('/user')
            }
        } catch (error) {
            const message = error.response?.data?.error
                ?? error.message
                ?? "Something went wrong. Please try again."
            setErr(message)
        }
    }

    return (
        <>
            <p>{action === 'login' ? 'Login' : 'Sign up'}</p>
            <div className={css['form-container']}>
                <form onSubmit={handleSubmit}>
                    {action === "login" ? null : (
                        <div className={css['input-label txt']}>
                            <label>Username</label> <br />
                            <input
                                name="username"
                                placeholder="e.g Jean Paul"
                                type="text"
                                onChange={handleChange}
                                value={formData.username}
                                required
                            /> <br />
                        </div>
                    )}

                    <div className={css['input-label txt']}>
                        <label>Email:</label> <br />
                        <input
                            name="email"
                            placeholder="e.g tmajor@xool.com"
                            type="email"
                            onChange={handleChange}
                            value={formData.email}
                            required
                        /> <br />
                    </div>
                    <div className={css['input-label txt']}>
                        <label>Password</label> <br />
                        <input
                            name="password"
                            placeholder="e.g tM@ior=yte"
                            type="password"
                            onChange={handleChange}
                            value={formData.password}
                            required
                        /> <br />
                    </div>
                    <input type="submit" value="Submit" />
                </form>
                <div className={css["action-btns"]}>
                    <button
                        type="button"
                        className={action === "login" ? css.active : ''}
                        onClick={() => setAction("login")}
                    >Login</button>
                    <button
                        type="button"
                        className={action === "signup" ? css.active : ''}
                        onClick={() => setAction("signup")}
                    >Sign Up</button>
                </div>
            </div>
            {err && <p className={css['error']}>{err}</p>}
        </>
    )
}
