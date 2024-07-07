import { createWebHistory, createRouter } from 'vue-router'

import Home from '../views/HomePage/Homeview.vue'
import product from '../views/Products/ProductShow.vue'
import produc_detail from '../views/Products/DetailPro.vue'
import LoginUser from '../views/Users/LoginUser.vue'
import CustomerOrder from '../views/Order/CustomerOrder.vue'
import ChangePassword from '../views/FogetPassword/ChangePassword.vue'
import register from '../views/Users/ReginsterAcc.vue'
import Viewprofile from '../views/profile/ProfileVue.vue'
import UserPostProduct from '../views/Products/ProductPostUser.vue'  
import ProductShow from '../views/Products/ProductShow.vue';
import addcard from '../views/CardAdd/addCard.vue';
import userchats from '../views/Users/Chat/ChatView.vue';
import booking from '../views/Order/InOrder.vue'
import payment from '../views/Order/PaymentOrder.vue'

const routes = [
    { 
        path: '/',
        name: 'Home', 
        component: Home
    },
    { 
        path: '/product',
        name: 'home_pro', 
        component: product
    },
    { 
        path: '/product/:id',
        name: 'produc_detail', 
        component: produc_detail,
        props: true
    },
    { 
        path: '/login',
        name: 'login', 
        component: LoginUser
    },
    {
        path: '/register',
        name: 'register', 
        component: register
    },
    { 
        path: '/order',
        name: 'order', 
        component: CustomerOrder
    },
    { 
        path: '/foget',
        name: 'foget', 
        component: ChangePassword
    },
    { 
        path: '/product-post',
        name:'product-post', 
        component: UserPostProduct
    },
    {
        path: '/profile',
        name: 'profile', 
        component: Viewprofile,
        children: [
            {
                path: '/userprodcuts',
                name: 'userprodcuts', 
                component: userprodcuts
            },
            {
                path: '/booking',
                name: 'booking', 
                component: booking
            },
        ]
    },
    {
        path: '/card',
        name: 'card', 
        component: addcard,
        props: true
    },
    {
        path: '/chats',
        name: '/chats', 
        component: userchats
    },
    {
        path: '/payment',
        name: 'payment',  
        component: payment
    }
    
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

export default router
