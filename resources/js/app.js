require('./bootstrap');
 
import { createApp } from 'vue'
import DraftIndex from './vue/draft/draft_index.vue'
import SchedulerIndex from './vue/scheduler/scheduler_index.vue'
import RecipeIndex from './vue/recipe/recipe_index.vue'
import vClickOutside from 'v-click-outside'

const draft_room = createApp({
    components: { DraftIndex, SchedulerIndex, RecipeIndex}
})

draft_room.directive('click-outside', {
    bind: function (el, binding, vnode) {
      el.clickOutsideEvent = function (event) {
        // here I check that click was outside the el and his children
        if (!(el == event.target || el.contains(event.target))) {
          // and if it did, call method provided in attribute value
          vnode.context[binding.expression](event);
        }
      };
      document.body.addEventListener('click', el.clickOutsideEvent)
    },
    unbind: function (el) {
      document.body.removeEventListener('click', el.clickOutsideEvent)
    },
  });
  
draft_room.mount('#app')