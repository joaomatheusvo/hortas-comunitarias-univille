import axios from 'axios'

const api = axios.create({
<<<<<<< HEAD
  baseURL: process.env.VUE_APP_API_URL || 'http://localhost:8181/api/v1',
  timeout: 15000,
=======
  baseURL: process.env.VUE_APP_API_URL || 'http://localhost:8000/api/v1',
  timeout: 10000,
>>>>>>> 7aeff65ddcb92b5566b83fe14c1b56ae9be32929
})

api.interceptors.request.use(
  config => {
    const token = localStorage.getItem('token')
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  error => Promise.reject(error)
)

api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      window.location.href = '/login'
    }
    return Promise.reject(error)
  }
)

export default api
