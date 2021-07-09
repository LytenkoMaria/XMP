<template>
    <div class="container">
        <img v-on:click.prevent="showPopup()"  class="add-directory" :src="'http://xmp/images/Gallery/Standart/add_directory.png'">
        <div class="directory-container">
            <ul class="list-inline">
                <li>
                    <span v-for="option in directories">
                        <div class="directory mb-1"  v-on:click.prevent="openDirectory(option.id)">
                            <a style="display:block">
                                <img class="directoryImg"  :src="'http://xmp/images/Gallery/Standart/directory.png'">
                            </a>
                            <p class="name-directory">{{ option.name }}</p>
                        </div>
                    </span>
                </li>
            </ul>
        </div>
        <b-modal id="modal-1" okTitle="Save" title="Create new directory" @ok="createDirectory()">
            <form>
                <div class="col mt-4">
                    <div class="form-row">
                        <div class="form-group col-md-3 ml-4 mt-1 text-center text-monospace">
                            <label>Name</label>
                        </div>
                        <div class="form-group col-md-7">
                            <input v-model="directoryName" type="text" class="form-control" required>
                        </div>
                    </div>
                </div>
            </form>
        </b-modal>
        <b-modal id="modal-success" cancel-hide class="modal fade bd-example-modal-sm" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true">
            <div class="success-container">
                <i v-if="success" class="fa fa-check-circle"></i>
                <i v-if="!success" class="fa fa-times-circle"></i>
                <label v-if="success" class="success-label" >Success</label>
                <label v-if="!success" class="error-label" >Directory already exists</label>
            </div>
        </b-modal>
    </div>
</template>

<script>
export default {
    name: "directory",
    props: ['openThisDirectory'],
    data() {
        return {
            directoryName: null,
            success: true,
            directories : [],
        };
    },
    methods: {
        showPopup: function () {
            this.$bvModal.show('modal-1');
            document.getElementById("modal-success-style").remove();
        },
        createDirectory: function () {
            axios.post('/directory/new', { name: this.directoryName})
                .then(
                    (response) => {
                        if (response.data.status == true) {
                            this.$bvModal.show('modal-success')
                            const style = document.createElement('style');
                            style.innerHTML = `
                            .modal-footer { visibility: hidden; height: 0px; }
                            .modal-header { visibility: hidden; height: 0px;}
                            .modal-content { background-color: #47c9a2; border-radius: 30px; width: 300px !important; }
                            .modal-backdrop {visibility: hidden;} `;
                            document.head.appendChild(style);
                            style.setAttribute('id', "modal-success-style");
                            this.success = true;
                        }
                        if (response.data.status == false) {
                            this.$bvModal.show('modal-success')
                            const style = document.createElement('style');
                            style.innerHTML = `
                            .modal-footer { visibility: hidden; height: 0px; }
                            .modal-header { visibility: hidden; height: 0px;}
                            .modal-content { background-color: #ff6666; border-radius: 30px; width: 370px !important; }
                            .modal-backdrop {visibility: hidden;} `;
                            document.head.appendChild(style);
                            style.setAttribute('id', "modal-success-style");
                            this.success = false;
                        }
                        this.directoryName = null;
                        this.getDirectory();
                    },
                    (error) => {
                        console.log(error.message);
                    }
                )
        },
        getDirectory : function () {
            axios.get('/directory/get')
                .then(
                    (response) => {
                        this.directories = response.data.directories;
                    },
                    (error) => {
                        console.log(error.message);
                    }
                )
        },
        openDirectory: function (id) {
            this.openThisDirectory({
                data: true,
                directory_id: id
            })
        },
    },
    created(){
        this.getDirectory();
    }
}
</script>

<style scoped>

</style>

