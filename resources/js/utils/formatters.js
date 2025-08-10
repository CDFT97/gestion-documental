/**
 * Format date to Spanish locale
 * @param {string} dateString - Date string to format
 * @param {object} options - Intl.DateTimeFormat options
 * @returns {string} Formatted date
 */
export const formatDate = (dateString, options = {}) => {
  if (!dateString) return 'N/A'
  
  const defaultOptions = {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  }
  
  return new Date(dateString).toLocaleDateString('es-ES', { ...defaultOptions, ...options })
}

/**
 * Format datetime to Spanish locale
 * @param {string} dateString - Date string to format
 * @returns {string} Formatted datetime
 */
export const formatDateTime = (dateString) => {
  if (!dateString) return 'N/A'
  
  return new Date(dateString).toLocaleString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

/**
 * Format file size in bytes to human readable format
 * @param {number} bytes - File size in bytes
 * @param {number} decimals - Number of decimal places
 * @returns {string} Formatted file size
 */
export const formatFileSize = (bytes, decimals = 2) => {
  if (!bytes || bytes === 0) return 'N/A'
  
  const k = 1024
  const dm = decimals < 0 ? 0 : decimals
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  
  return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i]
}

/**
 * Format numbers with Spanish locale
 * @param {number} number - Number to format
 * @param {object} options - Intl.NumberFormat options
 * @returns {string} Formatted number
 */
export const formatNumber = (number, options = {}) => {
  if (number === null || number === undefined) return 'N/A'
  
  return new Intl.NumberFormat('es-ES', options).format(number)
}

/**
 * Format currency with Spanish locale
 * @param {number} amount - Amount to format
 * @param {string} currency - Currency code (default: EUR)
 * @returns {string} Formatted currency
 */
export const formatCurrency = (amount, currency = 'EUR') => {
  if (amount === null || amount === undefined) return 'N/A'
  
  return new Intl.NumberFormat('es-ES', {
    style: 'currency',
    currency: currency
  }).format(amount)
}

/**
 * Format percentage
 * @param {number} value - Value to format as percentage
 * @param {number} decimals - Number of decimal places
 * @returns {string} Formatted percentage
 */
export const formatPercentage = (value, decimals = 1) => {
  if (value === null || value === undefined) return 'N/A'
  
  return new Intl.NumberFormat('es-ES', {
    style: 'percent',
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals
  }).format(value / 100)
}

/**
 * Format column type names to Spanish
 * @param {string} type - Column type
 * @returns {string} Formatted type name
 */
export const formatColumnType = (type) => {
  const types = {
    string: 'Texto',
    number: 'Número',
    integer: 'Entero',
    decimal: 'Decimal',
    float: 'Decimal',
    boolean: 'Booleano',
    date: 'Fecha',
    datetime: 'Fecha/Hora',
    timestamp: 'Marca de tiempo',
    text: 'Texto Largo',
    longtext: 'Texto Muy Largo',
    email: 'Email',
    url: 'URL',
    json: 'JSON',
    uuid: 'UUID'
  }
  return types[type] || type.charAt(0).toUpperCase() + type.slice(1)
}

/**
 * Get CSS classes for column type badges
 * @param {string} type - Column type
 * @returns {string} CSS classes
 */
export const getTypeClass = (type) => {
  const classes = {
    string: 'px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full',
    number: 'px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full',
    integer: 'px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full',
    decimal: 'px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full',
    float: 'px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full',
    boolean: 'px-2 py-1 text-xs font-medium bg-purple-100 text-purple-800 rounded-full',
    date: 'px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full',
    datetime: 'px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 rounded-full',
    timestamp: 'px-2 py-1 text-xs font-medium bg-orange-100 text-orange-800 rounded-full',
    text: 'px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full',
    longtext: 'px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full',
    email: 'px-2 py-1 text-xs font-medium bg-indigo-100 text-indigo-800 rounded-full',
    url: 'px-2 py-1 text-xs font-medium bg-cyan-100 text-cyan-800 rounded-full',
    json: 'px-2 py-1 text-xs font-medium bg-pink-100 text-pink-800 rounded-full',
    uuid: 'px-2 py-1 text-xs font-medium bg-teal-100 text-teal-800 rounded-full'
  }
  return classes[type] || classes.string
}

/**
 * Get CSS classes for status badges
 * @param {string} status - Status value
 * @returns {string} CSS classes
 */
export const getStatusBadge = (status) => {
  const badges = {
    completed: 'px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full',
    processing: 'px-2 py-1 text-xs font-medium bg-yellow-100 text-yellow-800 rounded-full',
    pending: 'px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full',
    failed: 'px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full',
    cancelled: 'px-2 py-1 text-xs font-medium bg-gray-100 text-gray-800 rounded-full'
  }
  return badges[status] || badges.pending
}

/**
 * Get status text in Spanish
 * @param {string} status - Status value
 * @returns {string} Status text in Spanish
 */
export const getStatusText = (status) => {
  const texts = {
    completed: 'Completado',
    processing: 'Procesando',
    pending: 'Pendiente',
    failed: 'Error',
    cancelled: 'Cancelado'
  }
  return texts[status] || 'Desconocido'
}

/**
 * Format cell value based on column type
 * @param {any} value - Cell value
 * @param {string} type - Column type
 * @returns {string} Formatted cell value
 */
export const formatCellValue = (value, type) => {
  if (value === null || value === undefined || value === '') {
    return '-'
  }

  switch (type) {
    case 'date':
      return formatDate(value)
    case 'datetime':
    case 'timestamp':
      return formatDateTime(value)
    case 'number':
    case 'integer':
    case 'decimal':
    case 'float':
      return typeof value === 'number' ? formatNumber(value) : value
    case 'boolean':
      return value ? 'Sí' : 'No'
    case 'email':
      return value
    case 'url':
      return value
    default:
      return value.toString()
  }
}

/**
 * Get CSS classes for cell styling based on type and value
 * @param {any} value - Cell value
 * @param {string} type - Column type
 * @returns {string} CSS classes
 */
export const getCellClass = (value, type) => {
  const baseClass = ''
  
  if (value === null || value === undefined || value === '') {
    return baseClass + ' text-gray-400 italic'
  }

  switch (type) {
    case 'number':
    case 'integer':
    case 'decimal':
    case 'float':
      return baseClass + ' font-mono text-right'
    case 'boolean':
      return baseClass + ' font-medium ' + (value ? 'text-green-600' : 'text-red-600')
    case 'date':
    case 'datetime':
    case 'timestamp':
      return baseClass + ' font-mono'
    case 'email':
      return baseClass + ' text-blue-600'
    case 'url':
      return baseClass + ' text-blue-600 underline'
    default:
      return baseClass
  }
}

/**
 * Truncate text to specified length
 * @param {string} text - Text to truncate
 * @param {number} length - Maximum length
 * @param {string} suffix - Suffix to add when truncated
 * @returns {string} Truncated text
 */
export const truncateText = (text, length = 50, suffix = '...') => {
  if (!text || text.length <= length) return text
  return text.substring(0, length) + suffix
}

/**
 * Pluralize Spanish words
 * @param {number} count - Count for pluralization
 * @param {string} singular - Singular form
 * @param {string} plural - Plural form (optional, will add 's' if not provided)
 * @returns {string} Pluralized text
 */
export const pluralize = (count, singular, plural = null) => {
  if (count === 1) return `${count} ${singular}`
  return `${count} ${plural || singular + 's'}`
}