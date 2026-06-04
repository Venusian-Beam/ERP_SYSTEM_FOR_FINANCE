<script setup>
import FinanceDetailWorkspace from '@/components/finance/FinanceDetailWorkspace.vue'
const details=[{label:'Primary Contact',value:'ap@northstar.com'},{label:'Payment Terms',value:'Net 30'},{label:'Credit Limit',value:'GHC 150,000.00'},{label:'Lifetime Value',value:'GHC 1,284,600.00'}]
const lines=[{description:'INV-2084',note:'Enterprise support services',account:'Accounts Receivable · 1100',qty:'1',rate:'GHC 48,200.00',amount:'GHC 48,200.00'},{description:'INV-2042',note:'Paid May 14, 2026',account:'Accounts Receivable · 1100',qty:'1',rate:'GHC 36,800.00',amount:'GHC 36,800.00'}]
const timeline=[{title:'Invoice INV-2084 sent',text:'Delivered to ap@northstar.com',time:'Jun 2 · 10:15',icon:'ri-mail-send-line'},{title:'Payment received',text:'Receipt RCT-260514-03 matched automatically',time:'May 14 · 14:28'},{title:'Credit limit reviewed',text:'Limit increased from GHC 100,000',time:'Apr 30 · 09:10',icon:'ri-bank-card-line'}]
</script>
<template><FinanceDetailWorkspace title="Northstar Retail Group" subtitle="Customer profile and receivables history" status="Active" amount-label="Open Balance" amount="GHC 48,200.00" :details="details" :lines="lines" :timeline="timeline" primary-action="Create Invoice" /></template>
