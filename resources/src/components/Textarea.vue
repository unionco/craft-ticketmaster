<template>
  <div class="field" :id="handle + '-field'">
    <div class="heading">
      <label :id="handle + '-label'" :for="handle">{{ label }}</label>
    </div>
    <div class="input ltr">
      <textarea
        class="text fullwidth"
        v-bind:class="inputClass"
        :type="type"
        :id="handle"
        :name="name"
        :value="value"
        :disabled="disabled"
        :rows="rows"
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
			rows: {
				type: Number,
				default: 5,
			},
			value: [String, Number],
      disabled: Boolean,
      hidden: Boolean
		}
	})
	export default class Textarea extends Vue {
		//
	}
</script>

<style lang="scss" module>
</style>
