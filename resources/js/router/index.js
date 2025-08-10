import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '../composables/useAuth'

const Login = () => import('../views/auth/Login.vue')
const Register = () => import('../views/auth/Register.vue')
const ForgotPassword = () => import('../views/auth/ForgotPassword.vue')
const ResetPassword = () => import('../views/auth/ResetPassword.vue')
const Dashboard = () => import('../views/Dashboard.vue')
const TableView = () => import('../views/excel/TableView.vue')
const Tables = () => import('../views/excel/Index.vue')
const Profile = () => import('../views/profile/Index.vue')

// Futuras vistas (las crearemos más adelante)
// const Tables = () => import('../views/Tables.vue')
// const TableDetail = () => import('../views/TableDetail.vue')
// const Documents = () => import('../views/Documents.vue')
// const DocumentViewer = () => import('../views/DocumentViewer.vue')

// Definir rutas
const routes = [
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
    meta: {
      requiresGuest: true,
      title: 'Iniciar Sesión',
      layout: 'auth'
    }
  },
  {
    path: '/register',
    name: 'Register',
    component: Register,
    meta: {
      requiresGuest: true,
      title: 'Registro',
      layout: 'auth'
    }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: {
      requiresAuth: true,
      title: 'Dashboard',
      breadcrumb: 'Dashboard'
    }
  },
  {
    path: '/forgot-password',
    name: 'ForgotPassword',
    component: ForgotPassword,
    meta: { 
      requiresGuest: true,
      title: 'Recuperar Contraseña',
      layout: 'auth'
    }
  },
  {
    path: '/reset-password',
    name: 'ResetPassword',
    component: ResetPassword,
    meta: { 
      requiresGuest: true,
      title: 'Nueva Contraseña',
      layout: 'auth'
    }
  },
  {
    path: '/tables',
    name: 'Tables',
    component: Tables,
    meta: {
      requiresAuth: true,
      title: 'Gestión de Tablas',
      breadcrumb: 'Tablas'
    }
  },
  {
    path: '/tables/:id',
    name: 'TableView',
    component: TableView,
    meta: {
      requiresAuth: true,
      title: 'Gestión de Tablas',
      breadcrumb: 'Tablas'
    }
  },
   {
    path: '/profile',
    name: 'Profile',
    component: Profile,
    meta: {
      requiresAuth: true,
      title: 'Mi Perfil',
      breadcrumb: 'Perfil'
    }
  },
  // // Gestión de documentos PDF
  // {
  //   path: '/documents',
  //   name: 'Documents',
  //   component: Documents,
  //   meta: {
  //     requiresAuth: true,
  //     title: 'Gestión de Documentos',
  //     breadcrumb: 'Documentos'
  //   }
  // },
  // {
  //   path: '/documents/:id',
  //   name: 'DocumentViewer',
  //   component: DocumentViewer,
  //   meta: {
  //     requiresAuth: true,
  //     title: 'Visor de Documentos',
  //     breadcrumb: 'Visor'
  //   },
  //   props: true
  // },
  {
    path: '/:pathMatch(.*)*',
    name: 'NotFound',
    component: () => import('../views/NotFound.vue'),
    meta: {
      title: 'Página no encontrada'
    }
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

let isNavigating = false


router.beforeEach(async (to, from, next) => {
  // Mostrar loading durante navegación
  isNavigating = true

  const { user, checkAuth } = useAuth()

  // Verificar autenticación solo si no tenemos usuario cargado
  if (!user.value) {
    await checkAuth()
  }

  // Rutas que requieren autenticación
  if (to.meta.requiresAuth && !user.value) {
    next({
      name: 'Login',
      query: { redirect: to.fullPath } // Guardar la ruta destino
    })
    return
  }

  // Rutas solo para invitados (login, register)
  if (to.meta.requiresGuest && user.value) {
    // Si viene de un redirect, ir allí; sino al dashboard
    const redirectTo = to.query.redirect || '/dashboard'
    next(redirectTo)
    return
  }

  // Actualizar título de página
  if (to.meta.title) {
    document.title = `${to.meta.title} - Gestión Documental`
  }

  next()
})

router.beforeResolve((to, from, next) => {
  // Este hook se ejecuta después de que todos los guards han sido resueltos
  // Útil para lógica adicional antes de la navegación
  next()
})

router.afterEach((to, from) => {
  // Ocultar loading después de navegación
  isNavigating = false
})

// Función helper para verificar si estamos navegando
export const useRouter = () => {
  return {
    router,
    isNavigating: () => isNavigating
  }
}

// Función helper para navegación programática con validación
export const navigateTo = (name, params = {}, query = {}) => {
  const { user } = useAuth()

  // Verificar si la ruta requiere auth
  const route = routes.find(r => r.name === name)
  if (route?.meta?.requiresAuth && !user.value) {
    router.push({ name: 'Login', query: { redirect: router.resolve({ name, params, query }).href } })
    return
  }

  router.push({ name, params, query })
}

// Función helper para el breadcrumb
export const useBreadcrumb = () => {
  const { currentRoute } = router

  const getBreadcrumb = () => {
    const matched = currentRoute.value.matched
    return matched
      .filter(route => route.meta?.breadcrumb)
      .map(route => ({
        text: route.meta.breadcrumb,
        name: route.name,
        path: route.path
      }))
  }

  return {
    breadcrumb: getBreadcrumb()
  }
}

export default router