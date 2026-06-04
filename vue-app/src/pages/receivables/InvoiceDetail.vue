<script setup>
import FinanceDetailWorkspace from '@/components/finance/FinanceDetailWorkspace.vue'
const details=[{label:'Customer',value:'Northstar Retail Group'},{label:'Issue Date',value:'June 2, 2026'},{label:'Due Date',value:'July 2, 2026'},{label:'Payment Terms',value:'Net 30'}]
const lines=[{description:'Enterprise support services',note:'June 2026 retainer',account:'Professional Services · 4100',qty:'1',rate:'GHC 42,000.00',amount:'GHC 42,000.00'},{description:'Priority incident coverage',note:'Add-on support package',account:'Support Revenue · 4120',qty:'1',rate:'GHC 6,200.00',amount:'GHC 6,200.00'}]
const timeline=[{title:'Invoice sent',text:'Delivered to ap@northstar.com',time:'Jun 2 · 10:15',icon:'ri-mail-send-line'},{title:'Invoice viewed',text:'Viewed through secure payment link',time:'Jun 2 · 11:04',icon:'ri-eye-line'},{title:'Reminder scheduled',text:'Automatic reminder set for June 27',time:'Jun 2 · 11:06',icon:'ri-notification-3-line'}]
</script>
<template><FinanceDetailWorkspace title="INV-2084" subtitle="Northstar Retail Group · Customer invoice" status="Open" amount-label="Amount Due" amount="GHC 48,200.00" :details="details" :lines="lines" :timeline="timeline" primary-action="Send Reminder" /></template>
