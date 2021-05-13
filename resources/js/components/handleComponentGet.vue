<template>
  <div class="container">

    <div class="card">
      <div class="card-header">
        {{ item.label }}
      </div>

      <div class="card-body">
        <div class="row">
          <div class="col-12">
              <div
                class="list-group-item"
                v-for="(element, idx) in list"
                :key="idx"
              >
                <div v-if="!item.datetime && !item.bool && !item.enumeration" class="input-group mb-3">
                  <input v-if="!item.readonly" type="text" v-model="element.text" class="xml form-control" placeholder="" aria-describedby="basic-addon2">
                  <input v-else type="text" v-model="element.text" class="form-control" placeholder="" aria-describedby="basic-addon2" readonly>
                  <div v-if="list.length>1" class="input-group-append">
                    <button class="btn btn-outline-secondary" @click="removeAt(idx)" type="button">Remove</button>
                  </div>
                </div>

                <div v-if="item.datetime">
                  <div class="row">
                    <div class="col">
                      <date-picker v-model="element.text" v-bind:value="element.text" :config="options"></date-picker>
                    </div>
                    <div class="col-3">
                      <div v-if="list.length>1" class="input-group-append">
                        <button class="btn btn-outline-secondary" @click="removeAt(idx)" type="button">Remove</button>
                      </div>
                    </div>
                  </div>
                </div>

                <div v-if="item.bool">
                  <div class="custom-control custom-checkbox mr-sm-2">
                    <input type="checkbox" v-model="element.text" class="custom-control-input" id="customControlAutosizing">
                    <label class="custom-control-label" for="customControlAutosizing">{{item.label}}</label>
                  </div>
                </div>
                <div v-if="item.enumeration">
                   <div v-if="!item.multiselect" class="form-group">
                    <label for="exampleFormControlSelect1">Select</label>
                    <select class="form-control" v-model="element.text" id="exampleFormControlSelect1">
                      <option v-for="element in item.enumeration">{{element}}</option>
                    </select>
                  </div>
                  <div v-else class="form-group">
                    <label for="exampleFormControlSelect2">Multiple select</label>
                    <select multiple class="form-control" v-model="element.selects" id="exampleFormControlSelect2">
                      <option v-for="element in item.enumeration">{{element}}</option>
                    </select>
                  </div>
                </div>
              </div>
          </div>
          <div class="col">
            <div v-if="item.append" class="col">
              <button class="btn btn-secondary button" @click="newAdd()">Add</button>
            </div>
          </div>
          <div class="col mt-2">
            <label> Include in XMP Template
            <input class="form-check-input" v-model="useInXMP" type="checkbox" value="" id="defaultCheck1">
          </label>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
    // Import required dependencies
  import 'bootstrap/dist/css/bootstrap.css';
  import '@fortawesome/free-solid-svg-icons';
  import '@fortawesome/fontawesome-svg-core';
  // Import this component
  import datePicker from 'vue-bootstrap-datetimepicker';

  // Import date picker css
  import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.css';
  import 'pc-bootstrap4-datetimepicker/build/css/bootstrap-datetimepicker.min.css';

    export default {
/*  components: {
  },*/
  props:
  {
     'item': null,
     editModal: Function,
  },
  data() {
    return {
      globalElId: 0,
      useInXMP: true,
      date: new Date(),
      options: {
        // defaultDate: [],
        format: this.item.bind,
        inline: false,
        sideBySide: false,
        useCurrent: true,

      },
      list: [],
      listNewItem: {},
    };
  },
  components: {
      datePicker
  },
  created() {
    this.$eventBus.$on('test', this.getChangeXMP);
    this.$eventBus.$on('getStructure', this.getStructure);
    this.$on('input', this.getInput);

    if (!this.item.append && this.item.type) {
      switch (this.item.type) {
        case "XmpBag":
          this.item.append = true;
          break;
        case "LangAlt":
          this.item.append = false;
          break;
        case "XmpSeq":
          this.item.append = true;
          break;
        case "XmpText":
          this.item.append = false;
          break;
        default:
          console.log("Unknown type");
      }
    }
    this.add();
    if (this.item.valueType) {
      switch (this.item.valueType) {
        case "URL":
          this.item.url = true;
          break;
        case "Date":
          this.item.datetime = this.item.bind;
          break;
        case "Boolean":
          this.item.bool = true;
          this.list[0].text = true;
          break;
        case "ClosedChoice":
            if (this.item.append) {
              this.item.append = false;
              this.item.multiselect = true;
            }
          break;
        case "OpenedChoice":
            if (this.item.append) {
              this.item.append = false;
              this.item.multiselect = true;
            }
          break;
        case "Text":
          break;
        default:
          console.log("Unknown type: " + this.item.valueType);
      }
    }

    if (this.item.url && !this.item.bind) {
      if (!this.item.text) {
          this.list[0].text = "https://";
      }
      else {this.list[0].text = this.item.text[0];}
      console.log('bind',this.item.text[0]);
    }
  },

  beforeDestroy(){
    this.$eventBus.$off('getStructure');
  },
  methods: {
    removeAt: function(idx) {
      this.list.splice(idx, 1);
      this.reIndexing();
    },
    getChangeXMP:  function() {
       this.listNewItem = {'item' : this.item, 'newText' : this.list};
       this.editModal(this.listNewItem);
    },
    add: function() {
        if ( this.item['text'] !== undefined) {
            if (this.item['text'].length>0 ) {
                for(var i = 0; i < this.item['text'].length; i++)
                {
                    this.globalElId++
                    this.list.push({ name: this.item.label, id: this.globalElId, text: this.item.text[i] , selects: [] });
                    this.reIndexing();
                }
            }
        }
        else {
        this.globalElId++
        this.list.push({ name: this.item.label, id: this.globalElId, text: this.item.bind , selects: [] });
        this.reIndexing();
        }
    },
    newAdd : function() {
        this.globalElId++;
        this.list.push({ name: this.item.label, id: this.globalElId, text: this.item.bind , selects: [] });
        this.reIndexing();
    },

    getStructure: function(id) {
      if (this.validate(id)) {
        var data = {};
        switch (this.item.type) {
          case "XmpBag":
            var arr = [];
              if (this.list[0].selects.length) {
                arr = this.list[0].selects;
              }
              else {
                this.list.forEach(function(item) {
                  arr.push(item.text.toString());
                });
              }

              data['rdf:Bag'] = [];
              data['rdf:Bag']= arr;
            break;
          case "LangAlt":
            var arr = this.list[0].text.toString();

              data['rdf:Alt'] = {};
              data['rdf:Alt']['@content'] = {};
              data['rdf:Alt']['@content'] = arr;

              var attr = {};
              attr['xml'] = {};
              attr['xml']['lang'] = "x-default";

              data['rdf:Alt']['@attributes'] = {};
              data['rdf:Alt']['@attributes'] = attr;
            break;
          case "XmpSeq":
              var arr = [];

              this.list.forEach(function(item) {
                arr.push(item.text.toString());
              });

              data['rdf:Seq'] = {};
              data['rdf:Seq'] = arr;

            break;
          case "XmpText":
            data = this.list[0].text.toString();
            break;
          default:
            console.log("Unknown type");
        }

        this.$eventBus.$emit('setStructure', data, this.item.name, this.item.prefix);
      }
    },

    reIndexing: function() {
      if (this.list.length > 1) {
        this.list.forEach(function(item, i) {
          this.list[i].name = this.item.label + " " + (i + 1);
        }.bind(this));
      }
      else {
        this.list[0].name = this.item.label;
      }
    },

    validate: function(id) {
      var basicRule = (this.useInXMP && id === this.item.id);

      if (!basicRule) {
        return false;
      }

      var result = false;

      if (this.list.length > 0) {
        if (this.list[0].selects && this.list[0].selects.length > 0) {
            result = true;
        }
        else {
          if (this.item.bool) {
            result = true;
          }
          else {
            var newList = [];
            this.list.forEach(function(item) {
              if (item.text && item.text !== "") {
                if (this.item.datetime) {
                  if (item.text !== this.item.bind) {
                    newList.push(item);
                  }
                }
                else {
                  newList.push(item);
                }

              }
            }.bind(this));

            if (newList.length !== 0) {
              result = true;
              this.list = newList;
            }
          }
        }
      }
      return result;
    }
  }
};

</script>
<style scoped>
.button {
  margin-top: 35px;
}
.handle {
  float: left;
  padding-top: 8px;
  padding-bottom: 8px;
}

.close {
  float: right;
  padding-top: 8px;
  padding-bottom: 8px;
}

input {
  display: inline-block;
  width: 50%;
}

.text {
  margin: 20px;
}
</style>
