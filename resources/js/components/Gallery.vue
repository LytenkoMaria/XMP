<template>
    <div class="container">

        <directory v-if="!show" :openThisDirectory="openThisDirectory"></directory>
        <directoryImages v-if="show" :directoryId='idDirectory' ></directoryImages>

    </div>
</template>

<script>
import directory from "./Directory";
import directoryImages from "./Directory-images";

export default {
    name: "gallery",
    components: {
        directory,
        directoryImages,
    },
    props: {
        directoryId: Number,
    },
    data() {
        return {
           show: false,
           idDirectory: null,
        };
    },
    methods: {
        openThisDirectory : function (data) {
           if(data.data == true)
           {
               this.show = true;
               this.idDirectory = data.directory_id;
           }
        }
    },
    created(){

    }
}
</script>

<style scoped>

</style>
