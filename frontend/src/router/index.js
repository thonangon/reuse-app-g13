import { createWebHistory, createRouter } from 'vue-router'

// import homePage from '@/views/HomePage.vue'
import produc_detail from '../views/Products/DetailPro.vue'
import LoginUser from '../views/Users/LoginUser.vue'
import CustomerOrder from '../views/Order/CustomerOrder.vue'
import ChangePassword from '../views/FogetPassword/ChangePassword.vue'


const routes = [
    // { 
    //     path: '/',
    //     name:'home', 
    //     component: homePage
    // },
    { 
        path: '/produc_detail',
        name:'produc_detail', 
        component: produc_detail
    },
    { 
        path: '/login',
        name:'login', 
        component: LoginUser
    },
    { 
        path: '/order',
        name:'order', 
        component: CustomerOrder
        path: '/foget',
        name:'foget', 
        component: ChangePassword
    },
 
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

export default router;