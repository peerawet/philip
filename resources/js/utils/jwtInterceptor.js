import axios from "axios";

function jwtInterceptor() {
  axios.interceptors.request.use((config) => {
    const token = window.localStorage.getItem("token");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  });


}

export default jwtInterceptor;
