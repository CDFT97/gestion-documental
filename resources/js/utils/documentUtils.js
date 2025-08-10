/**
 * Utilidades para manejo de documentos PDF
 */

/**
 * Validar archivo antes de subir
 * @param {File} file - Archivo a validar
 * @returns {Object} - Resultado de la validación
 */
export const validateFile = (file) => {
  const maxSize = 50 * 1024 * 1024 // 50MB
  const allowedTypes = ['application/pdf']
  const allowedExtensions = ['.pdf']

  const errors = []

  // Validar tipo MIME
  if (!allowedTypes.includes(file.type)) {
    errors.push('Solo se permiten archivos PDF')
  }

  // Validar extensión del archivo
  const fileName = file.name.toLowerCase()
  const hasValidExtension = allowedExtensions.some(ext => fileName.endsWith(ext))
  if (!hasValidExtension) {
    errors.push('El archivo debe tener extensión .pdf')
  }

  // Validar tamaño
  if (file.size > maxSize) {
    errors.push('El archivo no puede ser mayor a 50MB')
  }

  // Validar que el archivo no esté vacío
  if (file.size === 0) {
    errors.push('El archivo no puede estar vacío')
  }

  return {
    isValid: errors.length === 0,
    errors,
    fileInfo: {
      name: file.name,
      size: file.size,
      type: file.type,
      formattedSize: formatFileSize(file.size)
    }
  }
}

/**
 * Formatear tamaño de archivo en bytes a formato legible
 * @param {number} bytes - Tamaño en bytes
 * @param {number} decimals - Número de decimales (default: 2)
 * @returns {string} - Tamaño formateado
 */
export const formatFileSize = (bytes, decimals = 2) => {
  if (bytes === 0) return '0 Bytes'

  const k = 1024
  const dm = decimals < 0 ? 0 : decimals
  const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB']

  const i = Math.floor(Math.log(bytes) / Math.log(k))

  return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i]
}

/**
 * Extraer nombre del archivo sin extensión
 * @param {string} filename - Nombre del archivo
 * @returns {string} - Nombre sin extensión
 */
export const getFileNameWithoutExtension = (filename) => {
  if (!filename) return ''
  return filename.replace(/\.[^/.]+$/, '')
}

/**
 * Generar nombre único para el archivo
 * @param {string} originalName - Nombre original
 * @returns {string} - Nombre único
 */
export const generateUniqueFileName = (originalName) => {
  const nameWithoutExt = getFileNameWithoutExtension(originalName)
  const extension = originalName.split('.').pop()
  const timestamp = Date.now()
  const random = Math.random().toString(36).substring(2, 8)

  return `${nameWithoutExt}_${timestamp}_${random}.${extension}`
}

/**
 * Validar metadatos del documento
 * @param {Object} metadata - Metadatos a validar
 * @returns {Object} - Resultado de la validación
 */
export const validateDocumentMetadata = (metadata) => {
  const errors = []
  const cleaned = { ...metadata }

  // Validar nombre
  if (cleaned.name && cleaned.name.length > 255) {
    errors.push('El nombre no puede exceder 255 caracteres')
  }

  // Validar categoría
  if (cleaned.category && cleaned.category.length > 100) {
    errors.push('La categoría no puede exceder 100 caracteres')
  }

  // Validar descripción
  if (cleaned.description && cleaned.description.length > 1000) {
    errors.push('La descripción no puede exceder 1000 caracteres')
  }

  // Limpiar espacios en blanco
  Object.keys(cleaned).forEach(key => {
    if (typeof cleaned[key] === 'string') {
      cleaned[key] = cleaned[key].trim()
      if (cleaned[key] === '') {
        cleaned[key] = null
      }
    }
  })

  return {
    isValid: errors.length === 0,
    errors,
    cleanedData: cleaned
  }
}

/**
 * Formatear fecha para filtros
 * @param {Date|string} date - Fecha a formatear
 * @returns {string} - Fecha en formato YYYY-MM-DD
 */
export const formatDateForFilter = (date) => {
  if (!date) return ''

  const dateObj = date instanceof Date ? date : new Date(date)

  if (isNaN(dateObj.getTime())) return ''

  return dateObj.toISOString().split('T')[0]
}

/**
 * Formatear fecha para mostrar
 * @param {string} dateString - Fecha en string
 * @param {Object} options - Opciones de formato
 * @returns {string} - Fecha formateada
 */
export const formatDisplayDate = (dateString, options = {}) => {
  if (!dateString) return ''

  const date = new Date(dateString)

  if (isNaN(date.getTime())) return ''

  const defaultOptions = {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    ...options
  }

  return date.toLocaleDateString('es-ES', defaultOptions)
}

/**
 * Crear FormData para upload de documento
 * @param {File} file - Archivo a subir
 * @param {Object} metadata - Metadatos del documento
 * @returns {FormData} - FormData preparado
 */
export const createDocumentFormData = (file, metadata = {}) => {
  const formData = new FormData()

  formData.append('file', file)

  // Agregar metadatos si existen
  if (metadata.name) formData.append('name', metadata.name)
  if (metadata.category) formData.append('category', metadata.category)
  if (metadata.description) formData.append('description', metadata.description)

  return formData
}

/**
 * Limpiar parámetros de consulta removiendo valores vacíos
 * @param {Object} params - Parámetros a limpiar
 * @returns {Object} - Parámetros limpiados
 */
export const cleanQueryParams = (params) => {
  const cleaned = {}

  Object.keys(params).forEach(key => {
    const value = params[key]
    if (value !== null && value !== undefined && value !== '') {
      cleaned[key] = value
    }
  })

  return cleaned
}

/**
 * Generar URL de preview para documento
 * @param {string|number} documentId - ID del documento
 * @param {string} baseUrl - URL base de la API
 * @returns {string} - URL de preview
 */
export const generatePreviewUrl = (documentId, baseUrl) => {
  return `${baseUrl}/documents/${documentId}/preview`
}

/**
 * Extraer información de error de respuesta de API
 * @param {Object} error - Error de axios
 * @returns {string} - Mensaje de error
 */
export const extractErrorMessage = (error) => {
  if (!error.response) {
    return 'Error de conexión. Verifique su conexión a internet.'
  }

  const { data, status } = error.response

  // Errores de validación
  if (status === 422 && data.errors) {
    const errorMessages = Object.values(data.errors).flat()
    return errorMessages.join(', ')
  }

  // Mensaje específico del servidor
  if (data.message) {
    return data.message
  }

  // Mensajes por código de estado
  const statusMessages = {
    400: 'Solicitud incorrecta',
    401: 'No autorizado',
    403: 'Acceso prohibido',
    404: 'Recurso no encontrado',
    413: 'Archivo demasiado grande',
    422: 'Datos de entrada inválidos',
    429: 'Demasiadas solicitudes',
    500: 'Error interno del servidor',
    503: 'Servicio no disponible'
  }

  return statusMessages[status] || `Error ${status}`
}

/**
 * Debounce function para búsquedas
 * @param {Function} func - Función a ejecutar
 * @param {number} wait - Tiempo de espera en ms
 * @returns {Function} - Función con debounce
 */
export const debounce = (func, wait) => {
  let timeout

  return function executedFunction(...args) {
    const later = () => {
      clearTimeout(timeout)
      func(...args)
    }

    clearTimeout(timeout)
    timeout = setTimeout(later, wait)
  }
}

/**
 * Validar rango de fechas
 * @param {string} dateFrom - Fecha de inicio
 * @param {string} dateTo - Fecha de fin
 * @returns {Object} - Resultado de validación
 */
export const validateDateRange = (dateFrom, dateTo) => {
  const errors = []

  if (dateFrom && dateTo) {
    const from = new Date(dateFrom)
    const to = new Date(dateTo)

    if (from > to) {
      errors.push('La fecha de inicio debe ser anterior a la fecha de fin')
    }

    const now = new Date()
    if (from > now || to > now) {
      errors.push('Las fechas no pueden ser futuras')
    }
  }

  return {
    isValid: errors.length === 0,
    errors
  }
}

/**
 * Obtener extensión de archivo
 * @param {string} filename - Nombre del archivo
 * @returns {string} - Extensión en minúsculas
 */
export const getFileExtension = (filename) => {
  if (!filename) return ''
  return filename.split('.').pop().toLowerCase()
}

/**
 * Verificar si el archivo es PDF
 * @param {File|string} file - Archivo o nombre de archivo
 * @returns {boolean} - true si es PDF
 */
export const isPdfFile = (file) => {
  if (file instanceof File) {
    return file.type === 'application/pdf' || getFileExtension(file.name) === 'pdf'
  }

  if (typeof file === 'string') {
    return getFileExtension(file) === 'pdf'
  }

  return false
}