import Papa from 'papaparse'
import jsPDF from 'jspdf'
import 'jspdf-autotable'

/**
 * Export data to CSV
 * @param {Array} data - Array of objects to export
 * @param {Array} columns - Array of column definitions {label: 'Name', key: 'name'}
 * @param {String} filename - Name of the file
 */
export const exportToCSV = (data, columns, filename = 'export.csv') => {
  // Extract headers
  const headers = columns.map(col => col.label)
  
  // Format data based on columns
  const formattedData = data.map(row => {
    const rowData = {}
    columns.forEach(col => {
      let val = row[col.key]
      if (col.type === 'money') {
        val = 'GHC ' + Number(val || 0).toFixed(2)
      }
      rowData[col.label] = val
    })
    return rowData
  })

  // Generate CSV string
  const csv = Papa.unparse({
    fields: headers,
    data: formattedData
  })

  // Create blob and download
  const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  if (link.download !== undefined) {
    const url = URL.createObjectURL(blob)
    link.setAttribute('href', url)
    link.setAttribute('download', filename)
    link.style.visibility = 'hidden'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  }
}

/**
 * Export data to PDF
 * @param {Array} data - Array of objects to export
 * @param {Array} columns - Array of column definitions
 * @param {String} title - Document title
 * @param {String} filename - Name of the file
 */
export const exportToPDF = (data, columns, title = 'Report', filename = 'export.pdf') => {
  const doc = new jsPDF()

  // Add title
  doc.setFontSize(18)
  doc.text(title, 14, 22)
  doc.setFontSize(11)
  doc.setTextColor(100)
  doc.text(`Generated on: ${new Date().toLocaleDateString()}`, 14, 30)

  // Map columns for autotable
  const head = [columns.map(col => col.label)]
  
  // Map data body
  const body = data.map(row => {
    return columns.map(col => {
      let val = row[col.key]
      if (col.type === 'money') {
        return 'GHC ' + Number(val || 0).toFixed(2)
      }
      return val
    })
  })

  doc.autoTable({
    startY: 36,
    head: head,
    body: body,
    theme: 'grid',
    styles: { fontSize: 8, cellPadding: 3 },
    headStyles: { fillColor: [99, 102, 241], textColor: 255 }, // matches indigo theme
  })

  doc.save(filename)
}
