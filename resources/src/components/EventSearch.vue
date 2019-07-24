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
          <span v-if="suggestion.item._embedded.venues.length" class="light">
            – {{suggestion.item._embedded.venues[0].name}}, <span v-if="suggestion.item._embedded.venues[0].city">{{ suggestion.item._embedded.venues[0].city.name }}</span>, <span v-if="suggestion.item._embedded.venues[0].state">{{ suggestion.item._embedded.venues[0].state.name }}</span>
          </span>
        </template>
      </vue-autosuggest>
    </div>
    <div class="fields-container" v-if="event">
      <h2>Event Info</h2>
      <input type="hidden" :name="`fields[${options.handle}][tmEventId]`" :value="eventId" />
      <input type="hidden" :name="`fields[${options.handle}][title]`" :value="event.title || event.name" />

      <div class="tabpanel">
        <div class="tabpanel-tabs" role="tabs">
          <ul class="tabpanel-tablist" role="tablist">
            <li class="tabpanel-tab" :class="{ 'is-active': activeTabIndex === index }" v-for="(tab, index) in tabs" v-bind:key="index" @click="switchTab(index)">
              {{tab.label}}
            </li>
          </ul>

          <div class="tabpanel-content" v-for="(tab, index) in tabs" v-bind:key="index">
            <div :class="{ 'is-hidden' : activeTabIndex !== index }">
              <table v-if="tab.json">
                <FormGroup
                  v-for="(group, index) in tab.json"
                  v-bind:key="index"
                  :label="index"
                  :group="group"
                  :name="`fields[${options.handle}][payload]`"
                />
              </table>
              <div v-else v-for="(field, fieldIndex) in tab.fields" v-bind:key="fieldIndex">
                <div v-if="Array.isArray(field)">
                  <component
                    v-for="(sub, subfieldIndex) in field" v-bind:key="subfieldIndex"
                    v-bind:is="sub.use"
                    :label="sub.label"
                    :name="'fields[' + options.handle + ']' + sub.name"
                    :handle="sub.handle"
                    :value="sub.value"
                  />
                </div>
                <div v-else>
                  <component
                    v-bind:is="field.use"
                    :label="field.label"
                    :name="'fields[' + options.handle + ']' + field.name"
                    :handle="field.handle"
                    :value="field.value"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
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
  activeTabIndex = 0;

  // Getters
  // =====================================================================
  get eventFields () {
    if (typeof this.event === 'string') {
      return JSON.parse(this.event);
    }

    return this.event;
  }

  get eventId() {
    return this.eventFields.tmEventId || this.eventFields.id;
  }

  get payloadFields() {
    return get(this.eventFields, 'payload') || this.eventFields;
  }

  get inputProps () {
    return {
      onInputChange: this.onInputChange,
      class: 'text nicetext fullwidth',
      placeholder: t('Search for a venue'),
      initialValue: this.eventFields.title || this.initialValue,
    };
  }

  get tabs() {
    return [
      {
        label: 'General',
        fields: [
          { label: 'Type', handle: 'type', name: '[payload][type]', use: 'Input', value: get(this.payloadFields, 'type') },
          { label: 'Event Url', handle: 'url', name: '[payload][url]', use: 'Input', value: get(this.payloadFields, 'url') },
          { label: 'Event ID', handle: 'tmEventId', name: '[payload][tmEventId]', use: 'Input', value: this.eventId },
          { label: 'Info', handle: 'info', name: '[payload][info]', use: 'Textarea', value: get(this.payloadFields, 'info') },
          { label: 'TicketLimit Info', handle: 'ticketLimit', name: '[payload][ticketLimit][info]', use: 'Input', value: get(this.payloadFields, 'ticketLimit.info') },
          { label: 'Please Note', handle: 'pleaseNote', name: '[payload][pleaseNote]', use: 'Textarea', value: get(this.payloadFields, 'pleaseNote') },
          { label: 'Seat Map', handle: 'seatmap', name: '[payload][seatmap][staticUrl]', use: 'Input', value: get(this.payloadFields, 'seatmap.staticUrl') },
        ]
      },
      {
        label: 'Dates & Prices',
        fields: [
          { label: 'Start Date', handle: 'dates', name: '[payload][dates][start][localDate]', use: 'Input', value: get(this.payloadFields, 'dates.start.localDate') },
          { label: 'Start Time', handle: 'dates', name: '[payload][dates][start][localTime]', use: 'Input', value: get(this.payloadFields, 'dates.start.localTime') },
          { label: 'End Date', handle: 'dates', name: '[payload][dates][end][localDate]', use: 'Input', value: get(this.payloadFields, 'dates.end.localDate') },
          { label: 'End Time', handle: 'dates', name: '[payload][dates][end][localTime]', use: 'Input', value: get(this.payloadFields, 'dates.end.localTime') },
          { label: 'Timezone', handle: 'dates', name: '[payload][dates][timezone]', use: 'Input', value: get(this.payloadFields, 'dates.timezone') },
          { label: 'Status', handle: 'dates', name: '[payload][dates][status][code]', use: 'Input', value: get(this.payloadFields, 'dates.status.code') },
          ...(get(this.payloadFields, 'priceRanges') || []).map((ranges, index) => (
            [
              { label: 'Price Range Type', handle: `priceRanges-${index}-type`, name: `[payload][priceRanges][${index}][type]`, use: 'Input', value: ranges.type },
              { label: 'Price Range Currency', handle: `priceRanges-${index}-currency`, name: `[payload][priceRanges][${index}][currency]`, use: 'Input', value: ranges.currency },
              { label: 'Price Range Min', handle: `priceRanges-${index}-min`, name: `[payload][priceRanges][${index}][min]`, use: 'Input', value: ranges.min },
              { label: 'Price Range Max', handle: `priceRanges-${index}-max`, name: `[payload][priceRanges][${index}][max]`, use: 'Input', value: ranges.max },
            ]
          ))
        ]
      },
      {
        label: 'Images',
        fields: (get(this.payloadFields, 'images') || []).map((image, index) => ({
          label: `Image ${index + 1}`,
          handle: `image-${index}`,
          name: `[payload][images][${index}][url]`,
          use: 'Input',
          value: image.url
        }))
      },
      {
        label: 'Classifications',
        fields: (get(this.payloadFields, 'classifications') || []).map((classification, index) => (
          [
            { label: 'Classification Segment', handle: `classification-${index}-segment`, name: `[payload][classifications][${index}][segment][name]`, use: 'Input', value: classification.segment.name },
            { label: 'Classification Genre', handle: `classification-${index}-genre`, name: `[payload][classifications][${index}][genre][name]`, use: 'Input', value: classification.genre.name },
            { label: 'Classification Sub Genre', handle: `classification-${index}-subGenre`, name: `[payload][classifications][${index}][subGenre][name]`, use: 'Input', value: classification.subGenre.name },
            { label: 'Classification Type', handle: `classification-${index}-type`, name: `[payload][classifications][${index}][type][name]`, use: 'Input', value: classification.type.name },
            { label: 'Classification Sub Type', handle: `classification-${index}-subType`, name: `[payload][classifications][${index}][subType][name]`, use: 'Input', value: classification.subType.name },
          ]
        ))
      },
      {
        label: 'Misc',
        fields: null,
        json: {
          _embedded: get(this.payloadFields, '_embedded'),
          _links: get(this.payloadFields, '_links'),
        }
      }
    ];
  }

  created() {}

  mounted() {}

  switchTab(index) {
    this.activeTabIndex = index;
  }

  onSelected(option) {
    this.event = option.item;
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
          console.log([{ data: res._embedded.events }]);
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
  padding: 10px;
  border: 1px solid darken(#f5f5f5, 10%);
}

.tabpanel {
  width: 100%;

  &-content > div {
    border: 1px solid darken(#f5f5f5, 10%);
    border-top: none;
    padding: 16px;
  }

  &-tabs {
    display: flex;
    flex-direction: column;
  }

  &-tablist {
    border-bottom: 1px solid darken(#f5f5f5, 10%);
    display: flex;
  }

  &-tab {
    border-left: 1px solid darken(#f5f5f5, 10%);
    border-right: 1px solid darken(#f5f5f5, 10%);
    border-top: 1px solid darken(#f5f5f5, 10%);
    cursor: pointer;
    padding: 8px 16px;
    transition: background 0.2s ease;

    &.is-active,
    &:hover {
      background: darken(#f5f5f5, 10%);
    }
  }
}

.is-hidden {
  display: none;
}

.vue-tabpanel {
  border: 1px solid darken(#f5f5f5, 10%);
  padding: 10px;
}

table {
  width: 100%;
}
</style>
