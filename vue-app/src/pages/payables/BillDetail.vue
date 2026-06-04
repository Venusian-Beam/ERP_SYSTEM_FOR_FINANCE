<script setup>
import FinanceDetailWorkspace from '@/components/finance/FinanceDetailWorkspace.vue'
const details=[{label:'Vendor',value:'Global Logistics'},{label:'Bill Date',value:'June 1, 2026'},{label:'Due Date',value:'June 15, 2026'},{label:'Payment Terms',value:'Net 14'}]
const lines=[{description:'Regional freight services',note:'May distribution cycle',account:'Freight & Delivery · 6320',qty:'1',rate:'GHC 10,800.00',amount:'GHC 10,800.00'},{description:'Fuel surcharge',note:'Contract rate adjustment',account:'Freight & Delivery · 6320',qty:'1',rate:'GHC 1,650.00',amount:'GHC 1,650.00'}]
const timeline=[{title:'Bill captured by OCR',text:'Fields extracted with 97% confidence',time:'Jun 1 · 09:18',icon:'ri-scan-2-line'},{title:'Coding reviewed',text:'Ama Boateng confirmed expense accounts',time:'Jun 1 · 10:42'},{title:'Awaiting CFO approval',text:'Approval threshold exceeded',time:'Jun 2 · 08:15',icon:'ri-shield-user-line'}]
</script>
<template><FinanceDetailWorkspace title="BILL-1048" subtitle="Global Logistics · Vendor bill" status="Pending approval" amount-label="Balance Due" amount="GHC 12,450.00" :details="details" :lines="lines" :timeline="timeline" primary-action="Approve & Pay" /></template>
