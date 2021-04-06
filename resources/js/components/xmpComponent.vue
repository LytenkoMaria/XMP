<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">Example Component</div>

                    <div class="card-body">
                        <button class="btn btn-secondary button" @click="getXmp">Get</button>

                    </div>
                </div>
            </div>
        </div>
        <!-- <div v-for="atr in attributes">
            Element: {{getInfo(atr)}}<br>
        </div> -->
    </div>
</template>

<script>
    export default {
        mounted() {
            console.log('Component mounted.')
        },
        data() {
            return {
                xmpDescription : null,

            }
        },
        methods: {
            log: function(evt) {
                window.console.log(evt);
            },
            getXmp: function() {
                axios.get('/xmp/get')
                  .then((response) => {
                    var xmp = {};
                    if (response.data["@root"]) {
                        xmp[response.data["@root"]] = response.data['rdf:RDF'];
                        if (response.data["@attributes"]) {
                            xmp[response.data["@root"]]["@attributes"] = response.data["@attributes"];
                        }
                        this.xmpDescription = xmp['x:xmpmeta']['rdf:Description'];
                        //console.log(this.xmpDescription)
                        axios.post('/xmp/show', this.xmpDescription)
                            .then(
                                (response) => {
                                    console.log(response);
                                },
                                (error) => {
                                    console.log(error.message);
                                }
                            )
                        //console.log(xmp['x:xmpmeta']['rdf:Description']);
                    }
                    this.setXmp(xmp);
                    //this.getInfo(xmp)
                },
                (error) => {
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
               axios.post('/xmp/set', xmp)
                    .then(
                        (response) => {
                        //console.log(response.data);
                    },
                    (error) => {
                      console.log(error.message);
                    }
                )
            },

        }
    }
</script>
