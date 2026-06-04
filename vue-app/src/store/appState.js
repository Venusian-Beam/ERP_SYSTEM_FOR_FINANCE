import { reactive } from 'vue'

export const appState = reactive({
  isSidebarOpen: true,
  isMobileMenuOpen: false,
  user: null,
  isAuthenticated: false,
  
  toggleSidebar() {
    this.isSidebarOpen = !this.isSidebarOpen
  },
  
  toggleMobileMenu() {
    this.isMobileMenuOpen = !this.isMobileMenuOpen
  },
  
  setAuth(user) {
    this.user = user
    this.isAuthenticated = !!user
  },

  logout() {
    this.user = null
    this.isAuthenticated = false
    // Clear tokens or handle logout API call here later
  }
})
