import { createRouter, createWebHistory } from 'vue-router'

// Pages - Dashboard
import Dashboard from '@/pages/Dashboard.vue'

// Pages - Projects
import ProjectsList from '@/pages/projects/ProjectsList.vue'
import ProjectCreate from '@/pages/projects/ProjectCreate.vue'
import ProjectDetails from '@/pages/projects/ProjectDetails.vue'

// Pages - Initiation
import Kickoff from '@/pages/initiation/Kickoff.vue'
import Stakeholders from '@/pages/initiation/Stakeholders.vue'

// Pages - Agile
import Sprints from '@/pages/agile/Sprints.vue'
import Backlog from '@/pages/agile/Backlog.vue'
import AgileDefinitions from '@/pages/agile/AgileDefinitions.vue'

// Pages - Tasks
import TasksList from '@/pages/tasks/TasksList.vue'
import TasksKanban from '@/pages/tasks/TasksKanban.vue'
import Workflows from '@/pages/tasks/Workflows.vue'

// Pages - Resources
import Resources from '@/pages/resources/Resources.vue'
import TimeTracking from '@/pages/resources/TimeTracking.vue'
import Budget from '@/pages/resources/Budget.vue'
import Milestones from '@/pages/resources/Milestones.vue'
import ProjectGantt from '@/pages/resources/ProjectGantt.vue'

// Pages - Quality
import QaTesting from '@/pages/quality/QaTesting.vue'
import ProjectRisks from '@/pages/quality/ProjectRisks.vue'
import ChangeLog from '@/pages/quality/ChangeLog.vue'

// Pages - Reports
import Reports from '@/pages/reports/Reports.vue'
import Documents from '@/pages/reports/Documents.vue'
import LessonsLearned from '@/pages/reports/LessonsLearned.vue'

// Pages - Communication
import ProjectChat from '@/pages/communication/ProjectChat.vue'

const routes = [
  {
    path: '/',
    name: 'Dashboard',
    component: Dashboard,
    meta: { title: 'Dashboard', breadcrumb: 'Dashboard' }
  },
  // Auth
  {
    path: '/login',
    name: 'Login',
    component: () => import('@/pages/auth/Login.vue'),
    meta: { title: 'Login', breadcrumb: 'Login', layout: 'auth' }
  },
  {
    path: '/register',
    name: 'Register',
    component: () => import('@/pages/auth/Register.vue'),
    meta: { title: 'Register', breadcrumb: 'Register', layout: 'auth' }
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: () => import('@/pages/auth/ForgotPassword.vue'),
    meta: { title: 'Forgot Password', breadcrumb: 'Forgot Password', layout: 'auth' }
  },
  // Accounting
  {
    path: '/accounting/chart-of-accounts',
    name: 'ChartOfAccounts',
    component: () => import('@/pages/accounting/ChartOfAccounts.vue'),
    meta: { title: 'Chart of Accounts', breadcrumb: 'Chart of Accounts' }
  },
  {
    path: '/accounting/journal-entries',
    name: 'JournalEntries',
    component: () => import('@/pages/accounting/JournalEntries.vue'),
    meta: { title: 'Journal Entries', breadcrumb: 'Journal Entries' }
  },
  {
    path: '/accounting/journal-entries/:id',
    name: 'JournalEntryDetail',
    component: () => import('@/pages/accounting/JournalEntryDetail.vue'),
    meta: { title: 'Journal Entry Detail', breadcrumb: 'Journal Entry Detail' }
  },
  {
    path: '/accounting/general-ledger',
    name: 'GeneralLedger',
    component: () => import('@/pages/accounting/GeneralLedger.vue'),
    meta: { title: 'General Ledger', breadcrumb: 'General Ledger' }
  },
  // Payables
  {
    path: '/payables/vendors',
    name: 'Vendors',
    component: () => import('@/pages/payables/Vendors.vue'),
    meta: { title: 'Vendors', breadcrumb: 'Vendors' }
  },
  {
    path: '/payables/vendors/:id',
    name: 'VendorDetail',
    component: () => import('@/pages/payables/VendorDetail.vue'),
    meta: { title: 'Vendor Detail', breadcrumb: 'Vendor Detail' }
  },
  {
    path: '/payables/bills',
    name: 'Bills',
    component: () => import('@/pages/payables/Bills.vue'),
    meta: { title: 'Bills', breadcrumb: 'Bills' }
  },
  {
    path: '/payables/bills/:id',
    name: 'BillDetail',
    component: () => import('@/pages/payables/BillDetail.vue'),
    meta: { title: 'Bill Detail', breadcrumb: 'Bill Detail' }
  },
  {
    path: '/payables/payments',
    name: 'Payments',
    component: () => import('@/pages/payables/Payments.vue'),
    meta: { title: 'Payments', breadcrumb: 'Payments' }
  },
  // Receivables
  {
    path: '/receivables/customers',
    name: 'Customers',
    component: () => import('@/pages/receivables/Customers.vue'),
    meta: { title: 'Customers', breadcrumb: 'Customers' }
  },
  {
    path: '/receivables/customers/:id',
    name: 'CustomerDetail',
    component: () => import('@/pages/receivables/CustomerDetail.vue'),
    meta: { title: 'Customer Detail', breadcrumb: 'Customer Detail' }
  },
  {
    path: '/receivables/invoices',
    name: 'Invoices',
    component: () => import('@/pages/receivables/Invoices.vue'),
    meta: { title: 'Invoices', breadcrumb: 'Invoices' }
  },
  {
    path: '/receivables/invoices/:id',
    name: 'InvoiceDetail',
    component: () => import('@/pages/receivables/InvoiceDetail.vue'),
    meta: { title: 'Invoice Detail', breadcrumb: 'Invoice Detail' }
  },
  {
    path: '/receivables/receipts',
    name: 'Receipts',
    component: () => import('@/pages/receivables/Receipts.vue'),
    meta: { title: 'Receipts', breadcrumb: 'Receipts' }
  },
  // Treasury
  {
    path: '/treasury/bank-accounts',
    name: 'BankAccounts',
    component: () => import('@/pages/treasury/BankAccounts.vue'),
    meta: { title: 'Bank Accounts', breadcrumb: 'Bank Accounts' }
  },
  {
    path: '/treasury/reconciliation',
    name: 'Reconciliation',
    component: () => import('@/pages/treasury/Reconciliation.vue'),
    meta: { title: 'Reconciliation', breadcrumb: 'Reconciliation' }
  },
  {
    path: '/treasury/cash-forecast',
    name: 'CashForecast',
    component: () => import('@/pages/treasury/CashForecast.vue'),
    meta: { title: 'Cash Forecast', breadcrumb: 'Cash Forecast' }
  },
  // Finance Reports
  {
    path: '/reports/profit-loss',
    name: 'ProfitLoss',
    component: () => import('@/pages/reports/ProfitLoss.vue'),
    meta: { title: 'Profit and Loss', breadcrumb: 'Profit and Loss' }
  },
  {
    path: '/reports/balance-sheet',
    name: 'BalanceSheet',
    component: () => import('@/pages/reports/BalanceSheet.vue'),
    meta: { title: 'Balance Sheet', breadcrumb: 'Balance Sheet' }
  },
  {
    path: '/reports/cash-flow',
    name: 'CashFlowStatement',
    component: () => import('@/pages/reports/CashFlowStatement.vue'),
    meta: { title: 'Cash Flow Statement', breadcrumb: 'Cash Flow' }
  },
  {
    path: '/reports/audit-trail',
    name: 'AuditTrail',
    component: () => import('@/pages/reports/AuditTrail.vue'),
    meta: { title: 'Audit Trail', breadcrumb: 'Audit Trail' }
  },
  // Settings
  {
    path: '/settings/company',
    name: 'CompanySettings',
    component: () => import('@/pages/settings/Company.vue'),
    meta: { title: 'Company Settings', breadcrumb: 'Company' }
  },
  {
    path: '/settings/users',
    name: 'UsersSettings',
    component: () => import('@/pages/settings/Users.vue'),
    meta: { title: 'Users', breadcrumb: 'Users' }
  },
  {
    path: '/settings/roles',
    name: 'RolesSettings',
    component: () => import('@/pages/settings/Roles.vue'),
    meta: { title: 'Roles', breadcrumb: 'Roles' }
  },
  {
    path: '/settings/preferences',
    name: 'PreferencesSettings',
    component: () => import('@/pages/settings/Preferences.vue'),
    meta: { title: 'Preferences', breadcrumb: 'Preferences' }
  },
  // Projects
  {
    path: '/projects',
    name: 'ProjectsList',
    component: ProjectsList,
    meta: { title: 'Projects List', breadcrumb: 'Projects' }
  },
  {
    path: '/projects/create',
    name: 'ProjectCreate',
    component: ProjectCreate,
    meta: { title: 'Create Project', breadcrumb: 'Create Project' }
  },
  {
    path: '/projects/:id',
    name: 'ProjectDetails',
    component: ProjectDetails,
    meta: { title: 'Project Details', breadcrumb: 'Project Details' }
  },
  // Initiation
  {
    path: '/initiation/kickoff',
    name: 'Kickoff',
    component: Kickoff,
    meta: { title: 'Project Kick-Off', breadcrumb: 'Kick-Off' }
  },
  {
    path: '/initiation/stakeholders',
    name: 'Stakeholders',
    component: Stakeholders,
    meta: { title: 'Stakeholders', breadcrumb: 'Stakeholders' }
  },
  // Agile
  {
    path: '/agile/sprints',
    name: 'Sprints',
    component: Sprints,
    meta: { title: 'Sprints', breadcrumb: 'Sprints' }
  },
  {
    path: '/agile/backlog',
    name: 'Backlog',
    component: Backlog,
    meta: { title: 'Backlog', breadcrumb: 'Backlog' }
  },
  {
    path: '/agile/definitions',
    name: 'AgileDefinitions',
    component: AgileDefinitions,
    meta: { title: 'DoR / DoD', breadcrumb: 'Definitions' }
  },
  // Tasks
  {
    path: '/tasks',
    name: 'TasksList',
    component: TasksList,
    meta: { title: 'Task List', breadcrumb: 'Tasks' }
  },
  {
    path: '/tasks/kanban',
    name: 'TasksKanban',
    component: TasksKanban,
    meta: { title: 'Kanban Board', breadcrumb: 'Kanban' }
  },
  {
    path: '/tasks/workflows',
    name: 'Workflows',
    component: Workflows,
    meta: { title: 'Workflows', breadcrumb: 'Workflows' }
  },
  // Resources
  {
    path: '/resources/team',
    name: 'Resources',
    component: Resources,
    meta: { title: 'Team Resources', breadcrumb: 'Team' }
  },
  {
    path: '/resources/time-tracking',
    name: 'TimeTracking',
    component: TimeTracking,
    meta: { title: 'Time Tracking', breadcrumb: 'Time Tracking' }
  },
  {
    path: '/resources/budget',
    name: 'Budget',
    component: Budget,
    meta: { title: 'Budget', breadcrumb: 'Budget' }
  },
  {
    path: '/resources/milestones',
    name: 'Milestones',
    component: Milestones,
    meta: { title: 'Milestones', breadcrumb: 'Milestones' }
  },
  {
    path: '/resources/gantt',
    name: 'ProjectGantt',
    component: ProjectGantt,
    meta: { title: 'Gantt Chart', breadcrumb: 'Gantt' }
  },
  // Quality
  {
    path: '/quality/qa-testing',
    name: 'QaTesting',
    component: QaTesting,
    meta: { title: 'QA & Testing', breadcrumb: 'QA Testing' }
  },
  {
    path: '/quality/risks',
    name: 'ProjectRisks',
    component: ProjectRisks,
    meta: { title: 'Risks & Issues', breadcrumb: 'Risks' }
  },
  {
    path: '/quality/change-log',
    name: 'ChangeLog',
    component: ChangeLog,
    meta: { title: 'Change Log', breadcrumb: 'Change Log' }
  },
  // Reports
  {
    path: '/reports/analytics',
    name: 'Reports',
    component: Reports,
    meta: { title: 'Reports & Analytics', breadcrumb: 'Analytics' }
  },
  {
    path: '/reports/documents',
    name: 'Documents',
    component: Documents,
    meta: { title: 'Documents', breadcrumb: 'Documents' }
  },
  {
    path: '/reports/lessons-learned',
    name: 'LessonsLearned',
    component: LessonsLearned,
    meta: { title: 'Lessons Learned', breadcrumb: 'Lessons' }
  },
  // Communication
  {
    path: '/chat',
    name: 'ProjectChat',
    component: ProjectChat,
    meta: { title: 'Project Chat', breadcrumb: 'Chat' }
  },
  {
    path: '/unauthorized',
    name: 'Unauthorized',
    component: () => import('@/pages/errors/Unauthorized.vue'),
    meta: { title: 'Unauthorized', breadcrumb: 'Unauthorized', layout: 'blank' }
  },
  // Catch-all
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('@/pages/errors/NotFound.vue'),
    meta: { title: 'Page Not Found', breadcrumb: '404', layout: 'blank' }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

// Update page title on navigation
router.beforeEach((to, from, next) => {
  document.title = `Project Tracker - ${to.meta.title || 'Dashboard'}`
  next()
})

export default router

