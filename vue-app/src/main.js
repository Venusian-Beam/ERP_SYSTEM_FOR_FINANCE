import { createApp } from 'vue'
import App from './App.vue'
import router from './router'

// Import global styles
import './assets/styles/styles.css'
import './assets/styles/pm-custom.css'
import './assets/styles/main.css'
import './assets/styles/theme.css'
import './assets/styles/finance.css'

// Import Preline for dropdowns and interactive components
import './assets/js/preline.js'

const app = createApp(App)
app.use(router)

app.mount('#app')

