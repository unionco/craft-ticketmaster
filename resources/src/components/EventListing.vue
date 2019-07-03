<template>
  <div>
    <div class="wrapper">
      <md-drawer class="md-right" :md-active.sync="editEvent" md-swipeable>
        <md-toolbar class="md-transparent" md-elevation="0">
          <span class="md-title">Edit Event</span>
        </md-toolbar>
        <md-list v-if="activeEvent">
          <md-list-item>
            <Input
              label="Event Title"
              handle="event-title"
              :name="`events[${activeEvent.id}][title]`"
              :value="activeEvent.name"
            />
          </md-list-item>
          <md-list-item>
            <Redactor
              label="Event Info"
              handle="event-info"
              :name="`events[${activeEvent.id}][info]`"
              v-model="activeEvent.info"
            />
          </md-list-item>
        </md-list>
      </md-drawer>

      <md-table md-fixed-header v-model="searched" md-sort="unix" md-sort-order="asc" @md-selected="onSelect">
        <md-table-toolbar>
          <div class="md-toolbar-section-start">
            <md-button class="md-icon-button" @click="init">
              <md-icon>refresh</md-icon>
            </md-button>
            <h1 class="md-title">Events</h1>
          </div>
          <md-field md-clearable class="md-toolbar-section-end">
            <md-input placeholder="Search by name..." v-model="search" @input="searchOnTable" />
          </md-field>
        </md-table-toolbar>

        <md-table-toolbar slot="md-table-alternate-header" slot-scope="{ count }">
          <div class="md-toolbar-section-start">{{ getAlternateLabel(count) }}</div>

          <div class="md-toolbar-section-end">
            <md-button>
              Reject
            </md-button>
            <md-button @click="publish">
              Publish
            </md-button>
          </div>
        </md-table-toolbar>

        <md-table-empty-state
          md-label="No events found"
          :md-description="`No event found for this '${search}' query. Try a different search term.`">
        </md-table-empty-state>

        <md-table-row slot="md-table-row" slot-scope="{ item }" md-selectable="multiple">
          <md-table-cell class="md-table-cell-details" md-label="Name" md-sort-by="name">
            <div class="detailcontainer">
              <img :src="item.image" />
              <div>
                <h3>{{ item.name }}</h3>
                <p><strong>Categories:</strong> {{ item.genre }}</p>
                <p v-if="item.info" v-html="item.info" />
                <p v-if="item.pleaseNote"><strong>Please Note:</strong> {{ item.pleaseNote }}</p>
                <md-button @click="onEdit(item)" :md-ripple="false" class="md-default md-raised md-dense md-mini md-edit-button">Edit</md-button>
              </div>
            </div>
          </md-table-cell>
          <md-table-cell md-label="Date" md-sort-by="unix">{{ item.date }}</md-table-cell>
        </md-table-row>
      </md-table>
    </div>
  </div>
</template>

<script lang="js">
import { Component, Vue } from 'vue-property-decorator';
import qs from 'qs';
import Input from './Input';
import Redactor from './Redactor';

const toLower = text => (text.toString().toLowerCase());
const searchByName = (items, term) => {
  if (term) {
    return items.filter(item => toLower(item.name).includes(toLower(term)));
  }

  return items;
};

@Component({
  props: {
    venueId: String,
    apikey: String
  },
  components: {
    Input,
    Redactor
  }
})
export default class EventListing extends Vue {
  //
  search = '';
  searched = [];
  events = [];
  selected = [];
  editEvent = false;
  activeEvent = false;

  mounted() {
    this.init();
  }

  init() {
    this.fetchEvents();
  }

  genre(event) {
    const classification = event.classifications.shift();
    return classification ? classification.genre.name : 'Na';
  }

  getCategories(event) {
    const classification = event.classifications.find(c => c.primary);
    const cats = [];

    Object.keys(classification).forEach((key) => {
      if (classification[key].hasOwnProperty('name') && classification[key].name !== 'Undefined') {
        cats.push(classification[key].name);
      }
    });

    return cats.join(' / ');
  }

  getImage(event) {
    const image = event.images.find(i => i.ratio === '4_3');
    if (image) {
      return image.url;
    }
    return 'https://placehold.it/305x225?text=No+Image';
  }

  getDate(event) {
    const d = new Date(event.dates.start.dateTime);
    return {
      date: d.toLocaleString(),
      unix: d.getTime()
    };
  }

  transform(item) {
    const { id, name, type, url, info, pleaseNote } = item;
    const { date, unix } = this.getDate(item);

    return {
      id,
      name,
      type,
      url,
      info,
      pleaseNote,
      genre: this.getCategories(item),
      date,
      unix,
      image: this.getImage(item),
      payload: {
        ...item,
        venueId: this.$props.venueId,
      }
    };
  }

  transformCollection(collection) {
    return collection.map(item => this.transform(item));
  }

  fetchEvents() {
    const params = qs.stringify({
      apikey: this.$props.apikey,
      venueId: this.$props.venueId,
      source: 'ticketmaster',
      includeTBA: 'no',
      includeTBD: 'no',
      includeTest: 'no',
      size: '100',
    });

    fetch(`https://app.ticketmaster.com/discovery/v2/events.json?${params}`)
      .then(res => res.json())
      .then((res) => {
          if (res && res._embedded && res._embedded.events.length) {
            this.events = res._embedded.events;
            this.searched = this.transformCollection(this.events);
          }
      })
      .catch();
  }
  
  publish() {
    this.selected.forEach((event) => {
      console.log('save', event.payload);
    });
  }

  getAlternateLabel (count) {
    let plural = '';

    if (count > 1) {
      plural = 's';
    }

    return `${count} event${plural} selected`;
  }

  searchOnTable () {
    this.searched = this.transformCollection(searchByName(this.events, this.search));
  }

  onSelect(items) {
    this.selected = items;
  }

  onEdit(item) {
    this.activeEvent = item;
    this.editEvent = true;
  }
}
</script>

<style lang="scss" scoped>
.wrapper {
  position: relative;
}
.md-table {
  position: relative;
}
.md-table + .md-table {
  margin-top: 16px;
}
.md-table-alternate-header .md-toolbar {
  background-color: var(--md-theme-default-primary) !important;
}
.md-table-cell {
  height: unset;
}
.md-table-cell-details {
  width: 70%;
}
.md-edit-button {
  margin: 0 !important;
}
.md-drawer {
  width: 450px;
}
.detailcontainer {
  align-items: center;
  display: flex;

  h3 {
    font-size: 18px;
    margin: 0;
  }

  p {
    margin: 0;
    margin-bottom: 8px;
  }

  img {
    max-width: 150px;
    margin-right: 16px;
  }
}
</style>
