import LoadingSpinner from './LoadingSpinner.vue'
import Pagination from './Pagination.vue'
import ConfirmDialog from './ConfirmDialog.vue'

export {
  LoadingSpinner,
  Pagination,
  ConfirmDialog
}

export default {
  LoadingSpinner,
  Pagination,
  ConfirmDialog
}

// Plugin installation function for global registration
export const UIComponentsPlugin = {
  install(app) {
    app.component('LoadingSpinner', LoadingSpinner)
    app.component('Pagination', Pagination)
    app.component('ConfirmDialog', ConfirmDialog)
  }
}