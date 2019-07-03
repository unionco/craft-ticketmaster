<template>
  <div>
    <p>settings</p>
  </div>
</template>
<script lang="js">
import { Component, Vue } from 'vue-property-decorator';

@Component({
  props: {
    settings: Object
  },
  components: {}
})
export default class Settings extends Vue {
  //
}
</script>
<style lang="scss" scoped>
</style>
