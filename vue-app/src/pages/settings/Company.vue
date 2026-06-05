<script setup>
import { ref, onMounted } from 'vue'
import { settingsService } from '@/services/settingsService'
import SettingsWorkspace from '@/components/finance/SettingsWorkspace.vue'

const sections = ref([
  { title: 'Business Profile', description: 'Legal entity and contact information used on financial documents', icon: 'ri-building-line', fields: [
    { label: 'Legal Business Name', value: '' },
    { label: 'Trading Name', value: '' },
    { label: 'Tax Identification Number', value: '' },
    { label: 'Company Registration Number', value: '' },
    { label: 'Registered Address', value: '', wide: true },
  ]},
  { title: 'Financial Configuration', description: 'Defaults used throughout accounting and reporting', icon: 'ri-settings-4-line', fields: [
    { label: 'Base Currency', value: 'USD', type: 'select', options: ['USD', 'GHC', 'EUR', 'GBP', 'NGN', 'KES', 'ZAR', 'XOF'] },
    { label: 'Fiscal Year Start', value: 'January', type: 'select', options: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'] },
    { label: 'Accounting Method', value: 'Accrual', type: 'select', options: ['Accrual', 'Cash'] },
    { label: 'Default Tax Rate', value: '15%' },
    { label: 'Close Period Controls', value: 'Require administrator approval to reopen closed periods', type: 'toggle', enabled: true, wide: true },
  ]},
  { title: 'Team Members', description: 'Users with access to this company account', icon: 'ri-group-line', fields: [] },
])

const loading = ref(false)

onMounted(async () => {
  try {
    const companyData = await settingsService.users()
    const bp = sections.value[0].fields
    if (companyData.company) {
      bp[0].value = companyData.company.name || 'Kedebah Financial Services Ltd'
      bp[1].value = companyData.company.trading_name || 'Kedebah ERP'
      bp[2].value = companyData.company.tin || 'TIN-284-991-02'
      bp[3].value = companyData.company.reg_number || 'CS2849912021'
      bp[4].value = companyData.company.address || '14 Independence Avenue, Accra, Ghana'
    }
    if (companyData.users && companyData.users.length) {
      sections.value[2].fields = companyData.users.map(u => ({
        label: u.name,
        value: u.email,
        hint: u.role || 'Team member',
      }))
    }
  } catch (e) {
    console.error('Failed to load company data:', e)
  }
})

const handleSave = async () => {
  loading.value = true
  try {
    await settingsService.updateCompany({ sections: sections.value })
    console.log('Company settings saved')
  } catch (e) {
    console.error('Failed to save company settings:', e)
  } finally {
    loading.value = false
  }
}
</script>
<template><SettingsWorkspace title="Company Settings" subtitle="Manage legal entity, fiscal year, currency, and tax defaults" :sections="sections" @save="handleSave" /></template>
