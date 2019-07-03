<template>
  <div>
    <md-tabs>
      <md-tab id="tab-venue" md-label="Venue">
        <div class="toolbar">
          <div class="flex">
            <div class="flex-grow texticon search icon clearable">
              <VenueSearch 
                :apikey="apikey"
                @selected="onSelected"
              />
            </div>
          </div>
        </div>
        <div class="venue-details">
          <md-content v-show="isnew" class="md-light">
            <md-icon>search</md-icon>
            <h2>Search for a venue</h2>
          </md-content>
          <div v-show="!isnew">
            <VenueForm 
              :venue="venue" 
            />
          </div>
        </div>
      </md-tab>
      <md-tab id="tab-events" md-label="Events" :md-disabled="isnew ? true : false">
        <EventListing
          :apikey="apikey"
          :venueId="venue.tmVenueId || venue.id"
        />
      </md-tab>
    </md-tabs>
  </div>
</template>

<script lang="js">
import { Component, Vue } from 'vue-property-decorator';
import VenueSearch from './components/VenueSearch';
import VenueForm from './components/VenueForm';
import EventListing from './components/EventListing';

@Component({
  props: {
    venue: Object,
    apikey: String,
    isnew: Boolean
  },
  components: {
    VenueSearch,
    VenueForm,
    EventListing
  }
})
export default class App extends Vue {
  venue = {};
  apikey = '';
  isnew = true;
  tabs = [{
    label: 'Venue',
    id: 'venue',
    url: '#tab-venue',
    active: true
  }, {
    label: 'Events',
    id: 'events',
    url: '#tab-events',
    active: false
  }];

  created () {
    const { venue, apikey, isnew } = this.$props;

    this.venue = venue;
    this.apikey = apikey;
    this.isnew = isnew;
  }

  // venue selected
  onSelected(selection) {
    this.venue = {
      ...selection,
      payload: selection
    };
    this.isnew = false;
  }
}
</script>

<style lang="scss" scoped>
  .md-content {
    width: 100%;
    height: 200px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;

    .md-icon {
      margin: 0;
      opacity: 0.5;
    }

    h2 {
      font-size: 36px;
      opacity: 0.5;
      text-transform: uppercase;
    }
  }

  .md-tab {
    border-top: 1px solid #eee;
  }

  #tab-events {
    padding: 0 !important;
  }
</style>
