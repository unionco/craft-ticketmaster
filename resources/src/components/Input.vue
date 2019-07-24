<template>
  <div class="field" :id="handle + '-field'">
    <div class="heading">
      <label :id="handle + '-label'" :for="handle">{{ label }}</label>
    </div>
    <div class="input ltr">
      <input
        class="text fullwidth"
        v-bind:class="inputClass"
        :type="type"
        :id="handle"
        :name="name"
        :value="value"
        :disabled="disabled"
        maxlength="255"
        @input="$emit('input', $event)"
      />
    </div>
  </div>
</template>

<script lang="js">
  import { Component, Vue } from 'vue-property-decorator';
    
	@Component({
		props: {
      inputClass: String,
			label: String,
      name: String,
      handle: String,
			type: {
				type: String,
				default: 'text',
			},
			value: [String, Number],
      disabled: Boolean,
      hidden: Boolean
		}
	})
	export default class Input extends Vue {
		//
	}
</script>

<style lang="scss" scoped>
.field {
  padding: 8px 0;
  margin: 0;
  width: 100%;
}
</style>
