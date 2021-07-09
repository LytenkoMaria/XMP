<template>
    <div class="container">
        <div @change="changeRightImg" class="input__wrapper">
            <input  type="file" id="input__file" class="input input__file" multiple>
            <label for="input__file" class=" rad input__file-button hov profile-download-btn">
                                    <span class="input__file-icon-wrapper">
                                        <i class="fa fa-cloud-download" style="font-size: 30px"></i>
                                    </span>
                <span class=" input__file-button-text">Download</span>
            </label>
        </div>
        <p>{{ directoryId }}</p>
    </div>
</template>

<script>
export default {
    name: "directory-images",
    props: ['directoryId'],
    data() {
        return {
            show: false,
        };
    },
    methods: {

    },
    created(){
     //  console.log(this.directoryId);
    }
}
</script>

<style scoped>

</style>
