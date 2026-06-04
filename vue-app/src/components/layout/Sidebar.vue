<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRoute } from 'vue-router'

defineProps({
  open: {
    type: Boolean,
    default: true,
  },
})

const emit = defineEmits(['close'])
const route = useRoute()

const activeDropdown = ref(null)
const mobileMenuOpen = ref(false)
const mobileExpandedMenus = ref(['accounting'])

const menuItems = [
  {
    id: 'dashboard',
    label: 'Dashboard',
    icon: 'ri-home-line',
    to: '/',
  },
  {
    id: 'accounting',
    label: 'Accounting',
    icon: 'ri-book-2-line',
    children: [
      {
        label: 'Chart of Accounts',
        to: '/accounting/chart-of-accounts',
        icon: 'ri-git-branch-line',
        desc: 'Manage account structure'
      },
      {
        label: 'Journal Entries',
        to: '/accounting/journal-entries',
        icon: 'ri-article-line',
        desc: 'Double-entry bookkeeping'
      },
      {
        label: 'General Ledger',
        to: '/accounting/general-ledger',
        icon: 'ri-book-open-line',
        desc: 'Full transaction history'
      },
    ],
  },
  {
    id: 'payables',
    label: 'Payables',
    icon: 'ri-bank-card-line',
    children: [
      {
        label: 'Vendors',
        to: '/payables/vendors',
        icon: 'ri-user-received-line',
        desc: 'Manage supplier records'
      },
      {
        label: 'Bills',
        to: '/payables/bills',
        icon: 'ri-file-text-line',
        desc: 'Track & approve bills',
        badge: 3
      },
      {
        label: 'Payments',
        to: '/payables/payments',
        icon: 'ri-money-dollar-circle-line',
        desc: 'Schedule & process payments'
      },
    ],
  },
  {
    id: 'receivables',
    label: 'Receivables',
    icon: 'ri-file-list-3-line',
    children: [
      {
        label: 'Customers',
        to: '/receivables/customers',
        icon: 'ri-user-add-line',
        desc: 'Customer records & credit'
      },
      {
        label: 'Invoices',
        to: '/receivables/invoices',
        icon: 'ri-bill-line',
        desc: 'Create & track invoices',
        badge: 5
      },
      {
        label: 'Receipts',
        to: '/receivables/receipts',
        icon: 'ri-check-double-line',
        desc: 'Record payments received'
      },
    ],
  },
  {
    id: 'treasury',
    label: 'Treasury',
    icon: 'ri-safe-2-line',
    children: [
      {
        label: 'Bank Accounts',
        to: '/treasury/bank-accounts',
        icon: 'ri-bank-line',
        desc: 'Manage bank connections'
      },
      {
        label: 'Reconciliation',
        to: '/treasury/reconciliation',
        icon: 'ri-exchange-dollar-line',
        desc: 'Match & reconcile transactions'
      },
      {
        label: 'Cash Forecast',
        to: '/treasury/cash-forecast',
        icon: 'ri-line-chart-line',
        desc: 'Predict cash position'
      },
    ],
  },
  {
    id: 'reports',
    label: 'Reports',
    icon: 'ri-bar-chart-box-line',
    children: [
      {
        label: 'Profit & Loss',
        to: '/reports/profit-loss',
        icon: 'ri-pie-chart-line',
        desc: 'Income statement'
      },
      {
        label: 'Balance Sheet',
        to: '/reports/balance-sheet',
        icon: 'ri-scales-line',
        desc: 'Assets vs liabilities'
      },
      {
        label: 'Cash Flow',
        to: '/reports/cash-flow',
        icon: 'ri-water-flash-line',
        desc: 'Cash movement statement'
      },
      {
        label: 'Audit Trail',
        to: '/reports/audit-trail',
        icon: 'ri-shield-check-line',
        desc: 'Immutable activity log'
      },
    ],
  },
  {
    id: 'settings',
    label: 'Settings',
    icon: 'ri-settings-3-line',
    children: [
      {
        label: 'Company',
        to: '/settings/company',
        icon: 'ri-building-line',
        desc: 'Fiscal year & currency'
      },
      {
        label: 'Users',
        to: '/settings/users',
        icon: 'ri-team-line',
        desc: 'Manage team members'
      },
      {
        label: 'Roles',
        to: '/settings/roles',
        icon: 'ri-shield-user-line',
        desc: 'Permissions & access'
      },
      {
        label: 'Preferences',
        to: '/settings/preferences',
        icon: 'ri-equalizer-line',
        desc: 'System preferences'
      },
    ],
  },
]

// ─── Helpers ───────────────────────────────────────────────
const isActive = (path) => route.path === path

const isChildActive = (children) =>
  children?.some(child => route.path.startsWith(child.to))

const isGroupActive = (item) => {
  if (item.to) return isActive(item.to)
  return isChildActive(item.children)
}

// ─── Desktop Dropdown ──────────────────────────────────────
const openDropdown = (id) => {
  activeDropdown.value = id
}

const closeDropdown = () => {
  activeDropdown.value = null
}

const toggleDropdown = (id) => {
  activeDropdown.value = activeDropdown.value === id ? null : id
}

// Close dropdown on outside click
const handleOutsideClick = (e) => {
  if (!e.target.closest('.nav-item-wrap')) {
    activeDropdown.value = null
  }
}

// ─── Mobile Menu ───────────────────────────────────────────
const toggleMobileMenu = () => {
  mobileMenuOpen.value = !mobileMenuOpen.value
}

const closeMobileMenu = () => {
  mobileMenuOpen.value = false
}

const toggleMobileExpand = (id) => {
  const idx = mobileExpandedMenus.value.indexOf(id)
  if (idx > -1) {
    mobileExpandedMenus.value.splice(idx, 1)
  } else {
    mobileExpandedMenus.value.push(id)
  }
}

const isMobileExpanded = (id) =>
  mobileExpandedMenus.value.includes(id)

onMounted(() => {
  document.addEventListener('click', handleOutsideClick)
})

onUnmounted(() => {
  document.removeEventListener('click', handleOutsideClick)
})
</script>

<template>
  <!-- ─── Top Navigation Bar ─────────────────────────────── -->
  <header class="topnav">
    <div class="topnav-inner">

      <!-- Brand / Logo -->
      <div class="topnav-brand">
        <router-link to="/" class="brand-link" @click="closeMobileMenu">
          <img
            src="@/assets/images/Kedebah Logo.png"
            alt="Kedebah ERP"
            class="brand-logo"
          />
        </router-link>
      </div>

      <!-- Desktop Navigation -->
      <nav class="topnav-nav" aria-label="Main navigation">
        <ul class="nav-list">
          <li
            v-for="item in menuItems"
            :key="item.id"
            class="nav-item-wrap"
            @mouseenter="item.children && openDropdown(item.id)"
            @mouseleave="item.children && closeDropdown()"
          >
            <!-- Single Link (Dashboard) -->
            <router-link
              v-if="!item.children"
              :to="item.to"
              class="nav-link"
              :class="{ 'nav-link-active': isActive(item.to) }"
            >
              <i :class="item.icon" class="nav-link-icon"></i>
              <span>{{ item.label }}</span>
            </router-link>

            <!-- Dropdown Trigger -->
            <button
              v-else
              class="nav-link nav-link-btn"
              :class="{
                'nav-link-active': isChildActive(item.children),
                'nav-link-open':   activeDropdown === item.id
              }"
              @click="toggleDropdown(item.id)"
            >
              <i :class="item.icon" class="nav-link-icon"></i>
              <span>{{ item.label }}</span>
              <i
                class="ri-arrow-down-s-line nav-chevron"
                :class="{ 'chevron-up': activeDropdown === item.id }"
              ></i>
            </button>

            <!-- Mega Dropdown Panel -->
            <transition name="dropdown-fade">
              <div
                v-if="item.children && activeDropdown === item.id"
                class="dropdown-panel"
                @mouseenter="openDropdown(item.id)"
                @mouseleave="closeDropdown()"
              >
                <!-- Panel Header -->
                <div class="dropdown-header">
                  <div class="dropdown-header-icon">
                    <i :class="item.icon"></i>
                  </div>
                  <div>
                    <p class="dropdown-header-title">{{ item.label }}</p>
                    <p class="dropdown-header-sub">
                      {{ item.children.length }} modules available
                    </p>
                  </div>
                </div>

                <!-- Panel Items -->
                <div class="dropdown-items">
                  <router-link
                    v-for="child in item.children"
                    :key="child.to"
                    :to="child.to"
                    class="dropdown-item"
                    :class="{ 'dropdown-item-active': isActive(child.to) }"
                    @click="closeDropdown"
                  >
                    <div class="dropdown-item-icon">
                      <i :class="child.icon"></i>
                    </div>
                    <div class="dropdown-item-body">
                      <div class="dropdown-item-label-row">
                        <span class="dropdown-item-label">
                          {{ child.label }}
                        </span>
                        <span
                          v-if="child.badge"
                          class="dropdown-item-badge"
                        >
                          {{ child.badge }}
                        </span>
                      </div>
                      <span class="dropdown-item-desc">
                        {{ child.desc }}
                      </span>
                    </div>
                    <i
                      v-if="isActive(child.to)"
                      class="ri-check-line dropdown-item-check"
                    ></i>
                  </router-link>
                </div>
              </div>
            </transition>
          </li>
        </ul>
      </nav>

      <!-- Right Side: Period + User -->
      <div class="topnav-right">
        <!-- Current Period -->
        <div class="period-pill">
          <i class="ri-calendar-check-line"></i>
          <span>FY 2026</span>
        </div>

        <!-- Notifications -->
        <button class="icon-btn" aria-label="Notifications">
          <i class="ri-notification-3-line"></i>
          <span class="notif-dot"></span>
        </button>

        <!-- User Avatar -->
        <router-link to="/settings/users" class="user-btn" aria-label="User menu">
          <div class="user-avatar">PM</div>
          <span class="user-name">Patrick M.</span>
        </router-link>

        <!-- Mobile Hamburger -->
        <button
          class="mobile-menu-btn"
          aria-label="Toggle menu"
          @click="toggleMobileMenu"
        >
          <i :class="mobileMenuOpen
            ? 'ri-close-line'
            : 'ri-menu-line'"
          ></i>
        </button>
      </div>
    </div>

    <!-- ─── Mobile Menu Panel ──────────────────────────── -->
    <transition name="mobile-slide">
      <div v-if="mobileMenuOpen" class="mobile-menu">
        <div class="mobile-menu-inner">
          <ul class="mobile-nav-list">
            <li v-for="item in menuItems" :key="item.id">

              <!-- Single Link -->
              <router-link
                v-if="!item.children"
                :to="item.to"
                class="mobile-nav-link"
                :class="{ 'mobile-link-active': isActive(item.to) }"
                @click="closeMobileMenu"
              >
                <i :class="item.icon" class="mobile-nav-icon"></i>
                {{ item.label }}
              </router-link>

              <!-- Expandable Group -->
              <template v-else>
                <button
                  class="mobile-nav-link mobile-group-btn"
                  :class="{
                    'mobile-link-active': isChildActive(item.children),
                    'mobile-group-open':  isMobileExpanded(item.id)
                  }"
                  @click="toggleMobileExpand(item.id)"
                >
                  <i :class="item.icon" class="mobile-nav-icon"></i>
                  <span class="mobile-nav-label">{{ item.label }}</span>
                  <i
                    class="ri-arrow-down-s-line mobile-chevron"
                    :class="{ 'chevron-up': isMobileExpanded(item.id) }"
                  ></i>
                </button>

                <transition name="mobile-expand">
                  <ul
                    v-if="isMobileExpanded(item.id)"
                    class="mobile-submenu"
                  >
                    <li
                      v-for="child in item.children"
                      :key="child.to"
                    >
                      <router-link
                        :to="child.to"
                        class="mobile-child-link"
                        :class="{
                          'mobile-child-active': isActive(child.to)
                        }"
                        @click="closeMobileMenu"
                      >
                        <i :class="child.icon"></i>
                        <span>{{ child.label }}</span>
                        <span
                          v-if="child.badge"
                          class="mobile-badge"
                        >
                          {{ child.badge }}
                        </span>
                      </router-link>
                    </li>
                  </ul>
                </transition>
              </template>
            </li>
          </ul>

          <!-- Mobile Footer -->
          <div class="mobile-footer">
            <div class="mobile-user">
              <div class="user-avatar">PM</div>
              <div>
                <p class="mobile-user-name">Patrick M.</p>
                <p class="mobile-user-role">Chief Financial Officer</p>
              </div>
            </div>
            <div class="mobile-period">
              <i class="ri-calendar-check-line"></i>
              <span>Current Period: FY 2026</span>
            </div>
          </div>
        </div>
      </div>
    </transition>
  </header>
</template>

<style scoped>
/* ═══════════════════════════════════════════════════════════
   TOP NAVIGATION BAR
   Purple gradient brand matching your theme
   ═══════════════════════════════════════════════════════════ */

/* ─── Shell ───────────────────────────────────────────────── */
.topnav {
  position: sticky;
  top: 0;
  z-index: 200;
  background: linear-gradient(
    135deg,
    rgba(92, 97, 242, 0.98) 0%,
    rgba(72, 52, 189, 0.99) 100%
  );
  border-bottom: 1px solid rgba(255, 255, 255, 0.12);
  box-shadow:
    0 4px 24px rgba(38, 32, 112, 0.22),
    0 1px 0 rgba(255, 255, 255, 0.08);
  width: 100%;
  height: auto;
  min-height: 64px;
}

/* ─── Inner ───────────────────────────────────────────────── */
.topnav-inner {
  display: flex;
  align-items: center;
  height: 64px;
  padding: 0 1.5rem;
  gap: 0;
  max-width: 1600px;
  margin: 0 auto;
}

/* ─── Brand ───────────────────────────────────────────────── */
.topnav-brand {
  flex-shrink: 0;
  margin-right: 1.5rem;
}

.brand-link {
  display: flex;
  align-items: center;
  text-decoration: none;
}

.brand-logo {
  height: 40px;
  width: auto;
  object-fit: contain;
  filter: brightness(1) drop-shadow(0 2px 8px rgba(0,0,0,0.15));
}

/* ─── Desktop Nav ─────────────────────────────────────────── */
.topnav-nav {
  flex: 1;
}

.nav-list {
  display: flex;
  align-items: center;
  gap: 0.125rem;
  list-style: none;
  margin: 0;
  padding: 0;
  height: 64px;
}

/* ─── Nav Item Wrapper ────────────────────────────────────── */
.nav-item-wrap {
  position: relative;
  height: 100%;
  display: flex;
  align-items: center;
}

/* ─── Nav Link ────────────────────────────────────────────── */
.nav-link,
.nav-link-btn {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.45rem 0.875rem;
  border-radius: 0.5rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.82);
  text-decoration: none;
  border: none;
  background: none;
  cursor: pointer;
  white-space: nowrap;
  transition:
    color 0.16s ease,
    background 0.16s ease;
  height: 2.4rem;
}

.nav-link:hover,
.nav-link-btn:hover {
  color: #ffffff;
  background: rgba(255, 255, 255, 0.12);
}

/* Active state */
.nav-link-active {
  color: #ffffff !important;
  background: rgba(255, 255, 255, 0.18) !important;
  box-shadow: inset 0 -2px 0 rgba(255,255,255,0.6);
}

/* Open dropdown state */
.nav-link-open {
  color: #ffffff !important;
  background: rgba(255, 255, 255, 0.15) !important;
}

.nav-link-icon {
  font-size: 1rem;
  line-height: 1;
  flex-shrink: 0;
}

.nav-chevron {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.7);
  transition: transform 0.18s ease;
}

.chevron-up {
  transform: rotate(180deg);
}

/* ─── Dropdown Panel ──────────────────────────────────────── */
.dropdown-panel {
  position: absolute;
  top: calc(100% + 8px);
  left: 50%;
  transform: translateX(-50%);
  background: #ffffff;
  border: 1px solid rgba(99, 102, 241, 0.12);
  border-radius: 1rem;
  box-shadow:
    0 20px 48px rgba(38, 32, 112, 0.18),
    0 4px 16px rgba(0, 0, 0, 0.08);
  min-width: 280px;
  overflow: hidden;
  z-index: 300;
}

/* Panel Header */
.dropdown-header {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem 1.125rem 0.875rem;
  background: linear-gradient(
    135deg,
    rgba(92, 97, 242, 0.06),
    rgba(168, 85, 247, 0.06)
  );
  border-bottom: 1px solid rgba(99, 102, 241, 0.1);
}

.dropdown-header-icon {
  width: 2.25rem;
  height: 2.25rem;
  background: linear-gradient(135deg, #6366f1, #a855f7);
  border-radius: 0.625rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1rem;
  color: white;
  flex-shrink: 0;
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);
}

.dropdown-header-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: #1e293b;
  margin-bottom: 0.1rem;
}

.dropdown-header-sub {
  font-size: 0.725rem;
  color: #94a3b8;
}

/* Panel Items */
.dropdown-items {
  padding: 0.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.625rem 0.75rem;
  border-radius: 0.625rem;
  text-decoration: none;
  transition: background 0.15s ease;
  cursor: pointer;
  position: relative;
}

.dropdown-item:hover {
  background: rgba(99, 102, 241, 0.06);
}

.dropdown-item-active {
  background: rgba(99, 102, 241, 0.08);
}

.dropdown-item-icon {
  width: 2rem;
  height: 2rem;
  background: rgba(99, 102, 241, 0.08);
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.9375rem;
  color: #6366f1;
  flex-shrink: 0;
  transition: background 0.15s ease, color 0.15s ease;
}

.dropdown-item:hover .dropdown-item-icon,
.dropdown-item-active .dropdown-item-icon {
  background: linear-gradient(135deg, #6366f1, #a855f7);
  color: white;
}

.dropdown-item-body {
  flex: 1;
  min-width: 0;
}

.dropdown-item-label-row {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.dropdown-item-label {
  font-size: 0.8125rem;
  font-weight: 600;
  color: #1e293b;
  white-space: nowrap;
}

.dropdown-item:hover .dropdown-item-label,
.dropdown-item-active .dropdown-item-label {
  color: #6366f1;
}

.dropdown-item-desc {
  font-size: 0.725rem;
  color: #94a3b8;
  display: block;
  margin-top: 0.1rem;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.dropdown-item-badge {
  background: linear-gradient(135deg, #6366f1, #a855f7);
  color: white;
  font-size: 0.6rem;
  font-weight: 700;
  padding: 0.1rem 0.4rem;
  border-radius: 99px;
  min-width: 1.1rem;
  text-align: center;
}

.dropdown-item-check {
  color: #6366f1;
  font-size: 1rem;
  flex-shrink: 0;
}

/* ─── Right Side ──────────────────────────────────────────── */
.topnav-right {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-left: 1rem;
  flex-shrink: 0;
}

/* Period Pill */
.period-pill {
  display: flex;
  align-items: center;
  gap: 0.4rem;
  padding: 0.375rem 0.875rem;
  background: rgba(255, 255, 255, 0.12);
  border: 1px solid rgba(255, 255, 255, 0.18);
  border-radius: 99px;
  font-size: 0.8rem;
  font-weight: 600;
  color: rgba(255, 255, 255, 0.92);
  white-space: nowrap;
}

.period-pill i {
  font-size: 0.9rem;
}

/* Icon Button */
.icon-btn {
  position: relative;
  width: 2.25rem;
  height: 2.25rem;
  border: 1px solid rgba(255, 255, 255, 0.18);
  background: rgba(255, 255, 255, 0.1);
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 1.1rem;
  color: rgba(255, 255, 255, 0.88);
  cursor: pointer;
  transition: background 0.15s ease;
}

.icon-btn:hover {
  background: rgba(255, 255, 255, 0.18);
  color: white;
}

.notif-dot {
  position: absolute;
  top: 5px;
  right: 5px;
  width: 7px;
  height: 7px;
  background: #f43f5e;
  border-radius: 50%;
  border: 2px solid rgba(92, 97, 242, 0.98);
}

/* User Button */
.user-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.3rem 0.75rem 0.3rem 0.35rem;
  border: 1px solid rgba(255, 255, 255, 0.18);
  background: rgba(255, 255, 255, 0.1);
  border-radius: 99px;
  cursor: pointer;
  transition: background 0.15s ease;
  color: rgba(255, 255, 255, 0.9);
  font-size: 0.825rem;
  font-weight: 500;
}

.user-btn:hover {
  background: rgba(255, 255, 255, 0.18);
  color: white;
}

.user-avatar {
  width: 1.875rem;
  height: 1.875rem;
  background: rgba(255, 255, 255, 0.25);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.7rem;
  font-weight: 700;
  color: white;
  border: 1.5px solid rgba(255, 255, 255, 0.4);
  flex-shrink: 0;
}

.user-name {
  white-space: nowrap;
  font-size: 0.8rem;
}

/* Mobile Hamburger */
.mobile-menu-btn {
  display: none;
  width: 2.25rem;
  height: 2.25rem;
  border: 1px solid rgba(255, 255, 255, 0.2);
  background: rgba(255, 255, 255, 0.1);
  border-radius: 0.5rem;
  align-items: center;
  justify-content: center;
  font-size: 1.25rem;
  color: white;
  cursor: pointer;
  transition: background 0.15s ease;
}

.mobile-menu-btn:hover {
  background: rgba(255, 255, 255, 0.2);
}

/* ─── Mobile Menu ─────────────────────────────────────────── */
.mobile-menu {
  background: linear-gradient(
    180deg,
    rgba(72, 52, 189, 0.99),
    rgba(58, 38, 165, 1)
  );
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  max-height: calc(100vh - 64px);
  overflow-y: auto;
}

.mobile-menu-inner {
  padding: 1rem;
  max-width: 480px;
  margin: 0 auto;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.mobile-nav-list {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

/* Mobile Link */
.mobile-nav-link {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border-radius: 0.625rem;
  font-size: 0.9rem;
  font-weight: 500;
  color: rgba(255, 255, 255, 0.82);
  text-decoration: none;
  border: none;
  background: none;
  cursor: pointer;
  width: 100%;
  text-align: left;
  transition: background 0.15s ease, color 0.15s ease;
}

.mobile-nav-link:hover,
.mobile-link-active {
  color: white;
  background: rgba(255, 255, 255, 0.12);
}

.mobile-nav-icon {
  font-size: 1.1rem;
  width: 1.25rem;
  text-align: center;
  flex-shrink: 0;
}

.mobile-nav-label {
  flex: 1;
}

.mobile-chevron {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.6);
  transition: transform 0.18s ease;
  margin-left: auto;
}

.chevron-up {
  transform: rotate(180deg);
}

/* Mobile Submenu */
.mobile-submenu {
  list-style: none;
  margin: 0.25rem 0 0.25rem 1.5rem;
  padding: 0 0 0 0.75rem;
  border-left: 1px solid rgba(255, 255, 255, 0.18);
  display: flex;
  flex-direction: column;
  gap: 0.125rem;
}

.mobile-child-link {
  display: flex;
  align-items: center;
  gap: 0.625rem;
  padding: 0.5rem 0.75rem;
  border-radius: 0.5rem;
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.72);
  text-decoration: none;
  transition: background 0.15s ease, color 0.15s ease;
}

.mobile-child-link:hover,
.mobile-child-active {
  color: white;
  background: rgba(255, 255, 255, 0.1);
}

.mobile-child-link i:first-child {
  font-size: 0.9375rem;
  width: 1.125rem;
  text-align: center;
  flex-shrink: 0;
}

.mobile-badge {
  margin-left: auto;
  background: rgba(255, 255, 255, 0.2);
  color: white;
  font-size: 0.6rem;
  font-weight: 700;
  padding: 0.1rem 0.4rem;
  border-radius: 99px;
}

/* Mobile Footer */
.mobile-footer {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 1rem;
  background: rgba(28, 20, 96, 0.3);
  border-radius: 0.875rem;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.mobile-user {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.mobile-user-name {
  font-size: 0.875rem;
  font-weight: 600;
  color: white;
}

.mobile-user-role {
  font-size: 0.75rem;
  color: rgba(255, 255, 255, 0.6);
}

.mobile-period {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.8rem;
  color: rgba(255, 255, 255, 0.7);
  padding-top: 0.75rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
}

/* ─── Transitions ─────────────────────────────────────────── */
.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
  transition: opacity 0.15s ease, transform 0.15s ease;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(-6px);
}

.mobile-slide-enter-active,
.mobile-slide-leave-active {
  transition: max-height 0.25s ease, opacity 0.2s ease;
  overflow: hidden;
}

.mobile-slide-enter-from,
.mobile-slide-leave-to {
  max-height: 0;
  opacity: 0;
}

.mobile-slide-enter-to,
.mobile-slide-leave-from {
  max-height: 100vh;
  opacity: 1;
}

.mobile-expand-enter-active,
.mobile-expand-leave-active {
  transition: all 0.2s ease;
  overflow: hidden;
}

.mobile-expand-enter-from,
.mobile-expand-leave-to {
  max-height: 0;
  opacity: 0;
}

.mobile-expand-enter-to,
.mobile-expand-leave-from {
  max-height: 400px;
  opacity: 1;
}

/* ─── Responsive ──────────────────────────────────────────── */
@media (max-width: 1023px) {
  .topnav-nav   { display: none; }
  .period-pill  { display: none; }
  .user-name    { display: none; }

  .mobile-menu-btn {
    display: flex;
  }
}

@media (max-width: 640px) {
  .topnav-inner { padding: 0 1rem; }
  .icon-btn     { display: none; }
  .user-btn     { display: none; }
}
</style>
