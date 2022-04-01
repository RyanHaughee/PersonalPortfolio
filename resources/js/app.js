require('./bootstrap');
 
import { createApp } from 'vue'
import DraftIndex from './vue/draft_index.vue'

const draft_room = createApp({
    components: { DraftIndex }
})
  
draft_room.mount('#app')