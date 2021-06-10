<template>
  <div class="container">
      <div class="mt-2 row justify-content-end">
          <div v-if="keyGenerate == 'true'" class="col-md-4">
              <select  v-on:change="addListElem" v-model="selectElem" class="form-control list" id="exampleFormControlSelect1">
                  <option v-for="option in selectList" :disabled="option.disabled">
                      {{ option.name }} {{option.disabled}}
                  </option>
              </select>
          </div>
          <div class="col-md-8">
              <div class="d-flex flex-row justify-content-between">
                  <div v-if="keyGenerate == 'true'" class="block">
                      <div v-if="errors.input == true" class="alert alert-danger" role="alert">This field is require!</div>
                      <div class="input-group input-group-lg">
                          <div class="input-group-prepend">
                              <span class="input-group-text lable-in-text" id="inputGroup-sizing-lg">New name</span>
                          </div>
                          <input  v-model="fileName" type="text" class="form-control" aria-label="Large" aria-describedby="inputGroup-sizing-sm">
                      </div>
                  </div>
                  <div class="block" ml-4>
                      <div class="input__wrapper">
                          <input v-if="keyGenerate == 'true'" @click="generate" class="input input__file" multiple>
                          <input v-if="uploadLable == 'Upload'" @change="getXmp"  type="file" id="input__file" class="input input__file" multiple>
                          <input v-if="uploadLable == 'Change'" @click="changeXMP" class="input input__file" multiple>
                          <label for="input__file" id="button-change" class="input__file-button">
                              <span class="input__file-icon-wrapper">
                                  <img v-if="uploadLable == 'Upload' && keyGenerate == 'false'" src="http://xmp/images/Download.png" class="input__file-icon" width="30">
                                  <img v-if="uploadLable == 'Change' && keyGenerate == 'false'" src="http://xmp/images/Change1.png" class="input__file-icon" width="30">
                                  <img v-if="keyGenerate == 'true'" src="http://xmp/images/Create.png" class="input__file-icon" width="30">
                              </span>
                              <span class="input__file-button-text">{{uploadLable}}</span>
                          </label>
                      </div>
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
          <div class="xmp-scroll-body form-xml-card" >
            <div v-if="errors.xmp == true" class="alert alert-danger" role="alert">You try to upload empty xmp!</div>
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
      this.getTagsforSelect(this.lists);
      this.saveTags = this.lists;
      this.lists = this.lists.slice(0,1);
      console.log(this.selectList);
    $('#button-change').removeClass('change-btn-show');
    this.$eventBus.$on('setStructure', this.setStructure);
  },
  mounted: function(){
     this.$nextTick(this.openGenerate())
      const parentElem = $('#heading0');
      const childElem = parentElem[0].childNodes[0].childNodes[0];
      childElem.click();
  },
  beforeDestroy() {
    this.$eventBus.$off('setStructure');
  },
  data() {
    return {
      keyGenerate: false,
      i: null,
      errors: {
          input: false,
          xmp: false,
      },
      uploadLable: 'Upload',
      fileName: null,
      changeXMPitem: [],
      change: false,
      list2: [],
      list: [],
      saveTags: [],
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
      selectList: [],
      selectElem: null
    };
  },
  methods: {
    log: function(evt) {
        window.console.log(evt);
    },
    openGenerate : function() {
        this.keyGenerate = localStorage.getItem('generate');
        if(this.keyGenerate == 'true') { this.uploadLable = 'Save to event';}
    },
    getNewXMP: function(item) {
        this.changeXMPitem.push(item);
        ++this.i;
        let vm = this
        if (this.i === this.list2.length) {
            axios.post('/xmp/change', {'item': this.changeXMPitem, 'XMPforChange': this.xmp} )
                .then(
                    (response) => {
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
      if (this.list2.length > 0) {
        this.list2.forEach(function(item, i, list2) {
          this.$eventBus.$emit('getStructure', item.id);
        }.bind(this));
        this.send();
      }
      else {
          this.errors.xmp = true;
      }
      if(this.fileName == null){ this.errors.input = true; }
    },
    setStructure: function(item, name, prefix) {
      this.usedTags.push(prefix);
      this.items[prefix + ":" + name] = item;
    },
    send: function() {
      this.usedTags = this.unique(this.usedTags);
      var xmp = {};
      xmp['x:xmpmeta'] = {};
      xmp['x:xmpmeta']['rdf:Description'] = this.items;
      xmp['tags'] = this.usedTags;
      axios.post('/xmp/new/set', {'xmp':xmp, 'label': this.fileName})
          .then(
              (response) => {
                  localStorage.setItem('id_new_xmp', response.data.id_createXMP.original.status.id);
                  localStorage.setItem('generate', false);
                  localStorage.setItem('name_new_xmp', this.fileName);
                  window.location.href = '/Calendar?modal=true';
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
          }
          if (toId === this.lists[key].prefix) {
            this.lists[key].list.push(it);
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
          let uploadedFile = e.target.files[0];
          $('#button-change').addClass('change-btn-show');
          this.uploadLable = 'Change';
          const formData = new FormData()
          formData.append('attachment', uploadedFile)
          this.formData = formData;
          axios.post('/xmp/get', formData)
              .then(
                  (response) => {
                      this.changeImageName = response.data["imageName"];
                      var xmp = {};
                      if (response.data['extractXMP']["@root"]) {
                          xmp[response.data['extractXMP']["@root"]] = response.data['extractXMP']['rdf:RDF'];
                          if (response.data['extractXMP']["@attributes"]) {
                              xmp[response.data['extractXMP']["@root"]]["@attributes"] = response.data['extractXMP']["@attributes"];
                          }
                          this.xmpDescription = xmp['x:xmpmeta']['rdf:Description'];
                          this.xmp = xmp;
                          axios.post('/xmp/show', {'Description': this.xmpDescription, 'xmp': xmp , 'list': this.saveTags})
                              .then(
                                  (response) => {

                                      this.list2 = response.data.list2;
                                      this.saveList = response.data.list;
                                      for (let population in this.saveList) {
                                          this.mas = [response.data.list[population].list[0]]
                                          response.data.list[population].list = null;
                                          response.data.list[population].list = (this.mas[0]);
                                      }
                                      this.lists = response.data.list;

                                  },
                                  (error) => {
                                      console.log(error.message);
                                  }
                              )
                      }
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

                  },
                  (error) => {
                      console.log(error.message);
                  }
              )
      },
      changeXMP : function() {
        this.$eventBus.$emit('test', this.xmp);
      },
      getTagsforSelect : function( listSelect) {
          this.selectList = listSelect.slice(1);
      },
      addListElem : function() {
          let saveItem = null;
          let vm = this
          this.selectList.forEach(function(item, index) {
              if(vm.selectElem == item.name) {
                   saveItem = item;
                   vm.selectList[index].disabled = true
              }
          });
          if (saveItem) {
              this.lists.push(saveItem);
          }
      }
  }
};
</script>
<style scoped></style>
