import { createRouter, createWebHistory } from 'vue-router'
import { useAuth } from '../stores/auth'

const routes = [
  {
    path: '/',
    redirect: '/dashboard'
  },
  {
    path: '/login',
    name: 'Login',
    component: () => import('../views/auth/Login.vue'),
    meta: { guest: true }
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: () => import('../views/Dashboard.vue'),
    meta: { auth: true }
  },
  {
    path: '/quizzes',
    name: 'QuizList',
    component: () => import('../views/quizzes/QuizList.vue'),
    meta: { auth: true }
  },
  {
    path: '/quizzes/create',
    name: 'CreateQuiz',
    component: () => import('../views/quizzes/CreateQuiz.vue'),
    meta: { auth: true, role: ['admin', 'teacher'] }
  },
  {
    path: '/quizzes/:id',
    name: 'QuizDetail',
    component: () => import('../views/quizzes/QuizDetail.vue'),
    meta: { auth: true }
  },
  {
    path: '/quizzes/:id/take',
    name: 'TakeQuiz',
    component: () => import('../views/quizzes/TakeQuiz.vue'),
    meta: { auth: true, role: ['student'] }
  },
  {
    path: '/admin/users',
    name: 'UserManagement',
    component: () => import('../views/admin/UserManagement.vue'),
    meta: { auth: true, role: ['admin'] }
  },
  {
    path: '/admin/courses',
    name: 'CourseManagement',
    component: () => import('../views/admin/CourseManagement.vue'),
    meta: { auth: true, role: ['admin'] }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const auth = useAuth()
  const isAuthenticated = auth.isAuthenticated.value
  const userRole = auth.state.user?.role

  if (to.meta.auth && !isAuthenticated) {
    next('/login')
  } else if (to.meta.guest && isAuthenticated) {
    next('/dashboard')
  } else if (to.meta.role && !to.meta.role.includes(userRole)) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router
