<template>
  <div class="container">
      <div class="mt-2 row justify-content-center">
          <div class="col-md-8">
              <div class="card">
                  <div class="card-body">
                      <input type="file"   @change="getXmp"  class="btn form-control-file">
                      <button class="mt-3 btn btn-secondary button" @click="changeXMP">Change</button>
                  </div>
              </div>
          </div>
      </div>
    <div class="row mt-5">
      <div class="col-4">

        <div id="accordion">
          <div v-for="(tags, index) in lists" class="card">
            <div class="card-header" :id="getHeading(index)">
              <h5 class="mb-0">
                <button class="btn btn-link" data-toggle="collapse" :data-target="getCollaps(index, true)" :aria-expanded="index === 1" :aria-controls="getCollaps(index)">
                  {{ tags.name }}
                </button>
              </h5>
            </div>
            <div v-if="missList"></div> <!-- HACK -->
            <div :id="getCollaps(index)" class="collapse" v-bind:class="{ show: index == 1, collapsed: index !== 1 }" :aria-labelledby="getHeading(index)" data-parent="#accordion">
              <div class="card-body">
                <draggable
                  :id="tags.prefix"
                  class="dragArea list-group"
                  :list="tags.list"
                  ghost-class="ghost"
                  :group="{ name: 'elements', put: true }"
                  @change="log"

                >
                  <div
                    class="list-group-item"
                    v-for="element in tags.list"
                    :key="element.name"
                  >
                      <label></label>
                    {{ element.label }}
                  </div>
                </draggable>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-8">
        <div class="card">
          <div class="card-header">
            XMP
          </div>
          <div class="card-body form-xml-card" >
            <draggable
              class="dragArea list-group"
              :list="list2"
              group="elements"
              ghost-class="ghost"
              @change="log"
              id="xmpForm"
              :move="checkMove"
              @start="dragStart"
              @end="dragStop"
            >
              <div
               v-for="element in list2"
                :key="element.name"
              >
                <handle-component v-if="!change" v-bind:item=element></handle-component>
                <handle-component-get v-if="change" v-bind:item=element
                  :edit-modal = "getNewXMP"
                ></handle-component-get>
              </div>
            </draggable>
          </div>
        </div>
      </div>
  <!--
      <rawDisplayer class="col-3" :value="list1" title="List 1" />

      <rawDisplayer class="col-3" :value="list2" title="List 2" /> -->
    </div>
    <br>
    <div class="row">
      <div class="col">
        <button class="btn btn-secondary button" @click="generate" data-toggle="modal" data-target=".bd-example-modal-lg">Generate</button>
      </div>
    </div>

    <div  class="modal fade bd-example-modal-lg" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="false">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          {{xmpContent}}
        </div>
      </div>
    </div>

  </div>
</template>

<script>
import draggable from "vuedraggable";
import handleComponentGet from "./handleComponentGet";

export default {
  name: "clone",
  display: "Clone",
  order: 2,

  components: {
    draggable,
    handleComponentGet,
  },
  props: {
    lists: Array,
  },
  created() {
    if (this.lists) {
       // console.log(this.lists);
    }
    this.$eventBus.$on('setStructure', this.setStructure);
  },

  beforeDestroy() {
    this.$eventBus.$off('setStructure');
  },
  data() {
    return {
      i: null,
      changeXMPitem: [],
      change: false,
      list2: [],
      items: {},
      dragging: false,
      missList: false,
      missEvent: Object,
      xmpContent: null,
      usedTags: [],
      xmpDescription : null,
      samelist : [],
      uploadImage : null,
      xmp: {},
      changeImageName: null,
    };
  },
  methods: {
    log: function(evt) {
      window.console.log(evt);
    },
    getNewXMP: function(item) {
        this.changeXMPitem.push(item);
        ++this.i;
        let vm = this
        if (this.i === this.list2.length) {
            axios.post('/xmp/change', {'item': this.changeXMPitem, 'XMPforChange': this.xmp} )
                .then(
                    (response) => {
                        //console.log(response["data"]["newXMP"]);
                        console.log('this.formData',this.formData);
                        this.setXmp(response["data"]["newXMP"]);
                    },
                    (error) => {
                        console.log(error.message);
                    }
                )
            this.changeXMPitem=[];
            this.i = 0;
        }
    },
    generate: function() {
      this.items = {};
      console.log('go',this.list2);
      if (this.list2.length > 0) {
          console.log('list2',this.list2);
        this.list2.forEach(function(item, i, list2) {
          this.$eventBus.$emit('getStructure', item.id);
        }.bind(this));
        this.send();
      }
    },
    setStructure: function(item, name, prefix) {
        console.log('set')
      this.usedTags.push(prefix);
      this.items[prefix + ":" + name] = item;
    },
    send: function() {
      this.usedTags = this.unique(this.usedTags);
      var xmp = {};
      xmp['x:xmpmeta'] = {};
      xmp['x:xmpmeta']['rdf:Description'] = this.items;
      xmp['tags'] = this.usedTags;
      console.log(xmp);
      axios.post('/xmp/set', xmp)
          .then(
              (response) => {
              console.log('vvvv',response.data);
              this.xmpContent = response.data;
          },
          (error) => {
            console.log(error.message);
          }
      )
    },
    checkMove: function(e) {
      if (e.dragged._underlying_vm_.prefix !== e.to.id && e.to.id !== e.from.id) {
        this.missList = true;
        this.missEvent = e;
      }
      else {
        this.missList = false;
      }
    },
    dragStop: function() {
      if (this.dragging && this.missList) {

        Object.keys(this.lists).forEach(function (key) {
          var fromId = this.missEvent.to.id;
          var toId = this.missEvent.dragged._underlying_vm_.prefix;
          var id = this.missEvent.dragged._underlying_vm_.tag_id;
          var it = this.missEvent.dragged._underlying_vm_;

          if (fromId === this.lists[key].prefix) {
            this.removeByAttr(this.lists[key].list, 'tag_id', id);
            // console.log("Remove from " + fromId);
          }
          if (toId === this.lists[key].prefix) {
            this.lists[key].list.push(it);
            // console.log("Add to " + toId + "; ID = " + id);
          }
        }.bind(this));

        this.missList = false;
        this.missEvent = null;
      }
      this.dragging = false;
    },
    dragStart: function() {
      this.dragging = true;
    },
    removeByAttr: function(arr, attr, value){
      var i = arr.length;
      while(i--) {
         if( arr[i]
             && arr[i].hasOwnProperty(attr)
             && (arguments.length > 2 && arr[i][attr] === value)) {
             arr.splice(i,1);
         }
      }
      return arr;
    },
    unique: function(arr) {
      var obj = {};

      for (var i = 0; i < arr.length; i++) {
        var str = arr[i];
        obj[str] = true; // запомнить строку в виде свойства объекта
      }
      //console.log('ss',obj);
      return Object.keys(obj); // или собрать ключи перебором для IE8-
    },
    getCollaps: function(index, sharp = false) {
      return ((sharp ? "#" : "") + "collapse" + index);
    },
    getHeading: function(index) {
      return "heading" + index;
    },
      getXmp: function(e) {
          this.change = true;
          const config = { 'content-type': 'multipart/form-data' }
          let vm = this
          let uploadedFile = e.target.files[0]
          const formData = new FormData()
          formData.append('attachment', uploadedFile)
          this.formData = formData;
          axios.post('/xmp/get', formData)
              .then(
                  (response) => {
                      this.changeImageName = response.data["imageName"];
                      var xmp = {};
                      if (response.data['extractXMP']["@root"]) {
                          console.log(response)
                          xmp[response.data['extractXMP']["@root"]] = response.data['extractXMP']['rdf:RDF'];
                          if (response.data['extractXMP']["@attributes"]) {
                              xmp[response.data['extractXMP']["@root"]]["@attributes"] = response.data['extractXMP']["@attributes"];
                          }
                          this.xmpDescription = xmp['x:xmpmeta']['rdf:Description'];
                          this.xmp = xmp;
                          //console.log(xmp);
                          axios.post('/xmp/show', {'Description': this.xmpDescription, 'xmp': xmp})
                              .then(
                                  (response) => {

                                      this.list2 = response.data.list2;
                                      //console.log('List2',this.list2 );

                                  },
                                  (error) => {
                                      console.log(error.message);
                                  }
                              )
                      }
                      //console.log('fffffffffffffffffffff',this.formData);
                      //this.setXmp(xmp);
                      //this.getInfo(xmp)*/
                  },
                  (error) => {
                      console.log('errrr')
                      this.messages.push(error.message);
                  }
              )
      },
      getInfo: function(atr, name = "") {
          if(typeof atr === "object" && atr !== null) {
              var value = "";
              for (var key in atr) {
                  if(typeof atr[key] === "object" && atr !== null) {
                      var response = this.getInfo(atr[key], key)
                      if (response) value += response
                  } else {
                      if (atr[key]) value += "<pre>" + key + ": " + atr[key] + "</pre>";
                  }
              }
              if (value) document.write(value);
          }
          else if (atr) {
              document.write("<pre>" + name + " " + atr + "</pre>");
          }
      },
      setXmp: function(xmp) {
          axios.post('xmp/set', {'xmp':xmp, 'changeImageName': this.changeImageName } )
              .then(
                  (response) => {
                      console.log("aaaaaaaaaaaaaaaaaaa",response.data);
                  },
                  (error) => {
                      console.log(error.message);
                  }
              )
      },
      changeXMP : function() {
        this.$eventBus.$emit('test', this.xmp);
      }
  }
};
</script>
<style scoped></style>
