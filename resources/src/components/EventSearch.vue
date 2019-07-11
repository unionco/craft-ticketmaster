<template>
  <div>
    <div id="event-search" class="autosuggest-container">
      <vue-autosuggest
          :suggestions="suggestions"
          :getSuggestionValue="getSuggestionValue"
          :input-props="inputProps"
          :limit="limit"
          @selected="onSelected">
        <template slot-scope="{suggestion}">
          {{suggestion.item.name}}
          <span class="light">– {{suggestion.item._embedded.venues[0].name}}, {{ suggestion.item._embedded.venues[0].city.name }}, {{ suggestion.item._embedded.venues[0].state.name }}</span>
        </template>
      </vue-autosuggest>
    </div>
    <div class="fields-container" v-if="event">
      <h2>Additional Info</h2>
      <input type="hidden" :name="`fields[${options.handle}][tmEventId]`" :value="event.tmEventId || event.id" />
      <input type="hidden" :name="`fields[${options.handle}][title]`" :value="event.title || event.name" />
    <FormGroup
      v-for="(group, index) in payloadFields"
      v-bind:key="index"
      :label="index"
      :group="payloadFields[index]"
      :mapped="mapped"
      :name="`fields[${options.handle}][payload]`"
      mapName="fields[mapped]"
      mapNameDot="" />
    </div>
  </div>
</template>

<script lang="js">
import { Component, Vue } from 'vue-property-decorator';
import { VueAutosuggest } from 'vue-autosuggest';
import qs from 'qs';
import get from 'lodash.get';
import { t } from '../filters/translate';
import Input from './Input';
import Textarea from './Textarea';
import Redactor from './Redactor';
import FormGroup from './FormGroup';

@Component({
  components: {
    VueAutosuggest,
    Input,
    Textarea,
    Redactor,
    FormGroup
  },
  props: {
    options: Object,
    event: Object
  }
})
export default class EventSearch extends Vue {
  // Properties
  // =====================================================================
  suggestions = [{ data: [] }];
  limit = 10;
  initialValue = '';

  // Getters
  // =====================================================================
  get eventFields () {
    if (typeof this.event === 'string') {
      return JSON.parse(this.event);
    }

    return this.event;
  }

  get payloadFields() {
    return get(this.eventFields, 'payload') || this.eventFields;
  }

  get inputProps () {
    return {
      onInputChange: this.onInputChange,
      class: 'text nicetext fullwidth',
      placeholder: t('Search for a venue'),
      initialValue: this.initialValue,
    };
  }

  created() {
    console.log('created event search');
    // console.log(this.venue);
  }

  mounted() {
    console.log(this.eventFields);
  }

  onSelected(option) {
    this.event = option.item;
    console.log(this.eventFields);
    this.$emit('selected', option.item);
  }

  onInputChange(text) {
    if (text === '' || text === undefined || text.length < 3) {
      return;
    }

    clearTimeout(this.timeout);
    this.timeout = setTimeout(() => {
      this.search(text);
    }, 450);
  }

  search(value) {
    const params = qs.stringify({
      apikey: this.$props.options.apiKey,
      keyword: value
    });
    fetch(`https://app.ticketmaster.com/discovery/v2/events.json?${params}`)
      .then(res => res.json())
      .then((res) => {
        if (res && res._embedded && res._embedded.events.length) {
          this.suggestions = [{ data: res._embedded.events }];
        }
      })
      .catch();
  }

  getPayloadFieldName(handle) {
    return `fields[${this.options.handle}][payload]${handle}`;
  }

  getSuggestionValue(suggestion) {
    return suggestion.item.name || suggestion.item;
  }
}
</script>

<style lang="scss" scoped>
.fields-container {
  background: #eee;
  padding: 10px;
  border: 1px solid darken(#eee, 10%);
}
</style>
