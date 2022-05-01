<template>
  <Modal
    :show="open"
    maxWidth=""
    :style="{ maxWidth: '1200px', margin: 'auto' }"
  >
    <Card class="overflow-hidden flex flex-col h-full relative w-full">
      <div class="border-b border-gray-200 dark:border-gray-700 px-4 py-2 flex items-center">
        <Heading class="d-none hidden md:flex" level="3" v-text="__('Existing Media')" />

        <div class="pr-3 md:px-3 relative">
          <Icon
            type="search"
            class="inline-block absolute ml-2 text-gray-400"
            width="20"
            style="top:4px"
          />

          <input
            type="search"
            v-bind:placeholder="__('Search by name or file name')"
            class="appearance-none rounded-full h-8 pl-10 w-full bg-gray-100 dark:bg-gray-800 focus:bg-white focus:outline-none focus:ring"
            v-model="requestParams.search_text"
            @input="search"
            @change="search"
            @keydown.enter.prevent="search"
          />
        </div>

        <OutlineButton class="ml-auto" type="button" @click="close">
          {{ __('Close') }}
        </OutlineButton>
      </div>

      <div class="flex-grow overflow-x-hidden overflow-y-scroll flex flex-wrap p-2">
        <template v-if="items.length > 0">
          <div
            v-for="(item, key) in items"
            class="w-1/2 md:w-1/5 p-2"
          >
            <ExistingMediaItem
              :key="key"
              :item="item"
              @select="$emit('select', item); close()"
            ></ExistingMediaItem>
          </div>
        </template>

        <div v-if="loading" class="flex items-center justify-center z-50 p-6" style="min-height: 150px">
          <Loader class="text-60" />
        </div>

        <div v-else-if="items.length == 0" class="flex flex-col justify-center items-center px-6 py-8">
          <Icon type="search" class="mb-3 text-gray-300 dark:text-gray-500" width="50" height="50"></Icon>

          <h3 class="text-base font-normal mt-3">
            {{ __('No results found') }}
          </h3>
        </div>
      </div>

      <div
        class="flex-shrink border-t border-gray-200 dark:border-gray-700  px-4 py-2 flex justify-end"
        v-if="showNextPage"
      >
        <DefaultButton
          type="button"
          class="ml-auto"
          @click="nextPage"
        >
          {{ __('Load Next Page') }}
        </DefaultButton>
      </div>
    </Card>
  </Modal>
</template>

<script>
  import ExistingMediaItem from './ExistingMediaItem';
  import debounce from 'lodash/debounce';

  export default {
    components: {
      ExistingMediaItem,
    },

    data() {
      let aThis = this;
      return {
        requestParams: {
          search_text: '',
          page: 1,
          per_page: 15
        },
        items: [],
        response: {},
        loading: false,
        search: debounce(() => aThis.refresh(), 750),
      }
    },

    props: {
      open: {
        default: false,
        type: Boolean,
      },
    },

    computed: {
      showNextPage() {
        if (this.items.length == (this.requestParams.page * this.requestParams.per_page)) {
          return true;
        }

        return false;
      }
    },

    methods: {
      close() {
        this.$emit('close');
      },

      refresh() {
        this.requestParams.page = 1;
        return this.fireRequest().then(response => {
          this.items = response.data.data;
          return response;
        });
      },

      nextPage() {
        this.requestParams.page += 1;
        return this.fireRequest().then(response => {
          this.items = this.items.concat(response.data.data);
          return response;
        });
      },

      fireRequest() {
        // Set loading to true
        this.loading = true;

        return this.createRequest().then(response => {
          this.response = response;
          return response;
        }).finally(() => {
          // Set loading to false
          this.loading = false;
        });
      },

      /**
       * Request builders the request
       */
      createRequest() {
        return Nova.request().get(
          `/nova-vendor/ebess/advanced-nova-media-library/media`,
          {
            params: this.requestParams,
          }
        )
      }
    },

    watch: {
      open: function (newValue) {
        if (newValue) {
          if (this.items.length == 0) {
            this.refresh();
          }

          document.body.classList.add('overflow-x-hidden');
          document.body.classList.add('overflow-y-hidden');
        } else {
          document.body.classList.remove('overflow-x-hidden');
          document.body.classList.remove('overflow-y-hidden');
        }
      },
    },
  }
</script>
