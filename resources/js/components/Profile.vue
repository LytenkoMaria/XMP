<template>
    <div class="container">
<!--        <div class="border-cont"> </div>-->
        <div class="top-content">
            <img v-if="!setStandartTop" id="topImg" class="icon-profile">
            <div v-if="setStandartTop && change" class="empty-img-border-top">
                <i class="fa fa-picture-o empty-img-icon empty-img-top-icon"></i>
            </div>
            <div v-if="change" class="download-btn-container mb-4">
                <div class="row">
                    <div class="col-md-7" style=" margin-left: auto; margin-right: auto;">
                        <div @change="changeTopImg" v-if="change" style=" margin-left: 15%;" class="mt-3 input__wrapper">
                            <input  type="file" id="input__file" class="input input__file" multiple>
                            <label for="input__file" id="button-change" class="rad input__file-button hov profile-download-btn">
                                <span class="input__file-icon-wrapper">
                                    <i class="fa fa-cloud-download" style="font-size: 30px"></i>
                                </span>
                                <span class="input__file-button-text">{{ tegDownloadTop }}</span>
                            </label>
                        </div>
                    </div>
                    <div class="col-md-4 mt-3" >
                        <button v-on:click.prevent="delatedTopImg()" style="margin-left: -50% !important; margin-top: 0px !important;" type="button" class="btn rad btn-delated btn-danger">Delated</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-md-1"></div>
            <div class="col-md-4">
                <img v-if="!setStandartRight" id="rightImg" class="photograf">
                <div v-if="setStandartRight && change" class="empty-img-border">
                    <i class="fa fa-picture-o empty-img-icon"></i>
                </div>
                <div v-if="change" class="download-btn-container">
                    <div class="row">
                        <div class="col-md-7">
                            <div @change="changeRightImg" class="input__wrapper">
                                <input  type="file" id="input__file2" class="input input__file2" multiple>
                                <label for="input__file2" class=" rad input__file-button hov profile-download-btn">
                                    <span class="input__file-icon-wrapper">
                                        <i class="fa fa-cloud-download" style="font-size: 30px"></i>
                                    </span>
                                    <span class=" input__file-button-text">{{ tegDownloadRight }}</span>
                                </label>
                            </div>
                        </div>
                        <div class="col-md-1"></div>
                        <div class="col-md-4 mt-4" >
                            <button v-on:click.prevent="delatedRightImg()" type="button" class="btn rad btn-delated btn-danger">Delated</button>
                        </div>
                    </div>
                </div>
                <div v-if="!change" class="follow-me col-md-11 mt-4">
                    <p class="ml-1">Follow me</p>
                    <div class="row mt-4">
                        <div class="col-md-2"></div>
                        <a class="vk-link link-prof" href="" alt><i class="fa fa-vk media col-md-2"></i></a>
                        <a class="instagram-link link-prof" href="" alt><i class="fa fa-instagram  media col-md-2"></i></a>
                        <a class="facebook-link link-prof" href="" alt><i class="fa fa-facebook media col-md-2"></i></a>
                        <a class="twitter-link link-prof" href="" alt><i class="fa fa-twitter media col-md-2"></i></a>
                    </div>
                    <a v-if="!previewFlag" type="button" v-on:click.prevent="changeProfile()" class="mt-4 btn btn-sm animated-button thar-one">Change profile</a>
                </div>
                <div v-if="change" class="follow-me mt-4">
                    <p class="ml-1">Follow me</p>
                    <div class="row col-md-12 mt-4 ">
                        <i class="fa fa-vk change-media " style="color: #2787f5" disabled></i>
                        <input v-model="vk" type="text" class="form-control media-input" placeholder="Enter your VK">
                    </div>
                    <div class="row col-md-12 mt-4">
                        <i class="fa fa-instagram  change-media inst" disabled></i>
                        <input v-model="instagram" type="text" class="form-control media-input" placeholder="Enter your instagram">
                    </div>
                    <div class="row col-md-12 mt-4">
                        <i class="fa fa-facebook change-media" style="color: #4867aa" disabled></i>
                        <input v-model="facebook" type="text" class="form-control media-input" placeholder="Enter your facebook">
                    </div>
                    <div class="row col-md-12 mt-4">
                        <i class="fa fa-twitter change-media " style="color: #5da9dd" disabled></i>
                        <input v-model="twitter" type="text" class="form-control media-input" placeholder="Enter your twitter">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <profile-editor-component
                    :show='change'
                    :saveChangeProfile='saveChangeProfile'
                    :cancel='cancel'
                    :preview='preview'
                ></profile-editor-component>

                <div v-if="!change && !previewFlag" class="profile-text"></div>
                <div class="preview-text"></div>
                <div v-if="setText && !change" class="standart-text">
                    <h3>&nbsp; &nbsp; <tt>About me</tt></h3>
                    <hr />
                    <pre><tt>
                    Phone: **********
                    Email: **********</tt>
                    </pre>
                    <hr />
                    <p>&nbsp;</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2"></div>
            <div class="col-md-4">
                <a v-if="previewFlag" type="button" v-on:click.prevent="saveChangeProfile({data: newText})" class="mt-4 btn btn-sm animated-button thar-one">Save changes</a>
            </div>
            <div class="col-md-4">
                <a v-if="previewFlag" type="button" v-on:click.prevent="cancel({cancel: false})" class="mt-4 btn btn-sm animated-button thar-one cancel">Cancel</a>
            </div>
        </div>
    </div>
</template>

<script>
import editor from "./profile-editorComponent";

export default {
    name: "Profile",
    components: {
        editor,
    },
    props: {
        show: Boolean,
    },
    data() {
        return {
            tegDownloadTop: "Download",
            tegDownloadRight: "Download",
            previewFlag: false,
            vk: null,
            instagram: null,
            facebook: null,
            twitter: null,
            change: false,
            rightImg: null,
            topImg: null,
            profileTopImg: null,
            profileRightImg: null,
            setStandartTop: false,
            setStandartRight: false,
            setText: false,
            newText: null,
        };
    },
    methods: {
        changeProfile: function () {
            this.change = true;
        },
        changeTopImg: function (e) {
            let uploadedFile = e.target.files[0]
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('topImg');
                output.src = reader.result;
            };
            reader.readAsDataURL(e.target.files[0]);
            const formData = new FormData()
            this.topImg = uploadedFile
            this.tegDownloadTop = uploadedFile.name
            this.setStandartTop = false;
        },
        changeRightImg: function (e) {
            let uploadedFile = e.target.files[0]
            var reader = new FileReader();
            reader.onload = function(){
                var output = document.getElementById('rightImg');
                output.src = reader.result;
            };
            reader.readAsDataURL(e.target.files[0]);
            const formData = new FormData()
            this.rightImg = uploadedFile
            this.tegDownloadRight = uploadedFile.name
            this.setStandartRight = false;
        },
        saveChangeProfile (data) {
            const config = { 'content-type': 'multipart/form-data' }
            const formData = new FormData()
            formData.append('attachmentTop', this.topImg)
            formData.append('attachmentRight', this.rightImg)
            formData.append('text', data.data)
            formData.append('vk', this.vk)
            formData.append('instagram', this.instagram)
            formData.append('facebook', this.facebook)
            formData.append('twitter', this.twitter)
            axios.post('/profile/new', formData, config)
                .then(
                    (response) => {
                        $('.profile-text').empty();
                        $('.preview-text').empty();
                        this.getProfile();
                        this.change = false;
                        this.setText = true;
                        this.tegDownloadTop = "Download";
                        this.tegDownloadRight = "Download";
                        this.previewFlag = false;
                    },
                    (error) => {
                        console.log(error.message);
                    }
                )
        },
        getProfile: function () {
            axios.get('/profile/get')
                .then(
                    (response) => {

                        if (response.data.profile == false)
                        {
                            this.setStandartRight = true;
                            this.setStandartTop = true;
                            this.setText = false;
                        }
                        if(response.data.profile[0].image_top) {
                            this.profileTopImg = response.data.profile[0].image_top;
                            var output = document.getElementById('topImg');
                            output.src = 'http://xmp/images/Profile/'+this.profileTopImg;
                            this.setStandartTop = false;
                        }
                        else { this.setStandartTop = true; }
                        if(response.data.profile[0].image_right) {
                            this.profileRightImg = response.data.profile[0].image_right;
                            var output = document.getElementById('rightImg');
                            output.src = 'http://xmp/images/Profile/'+this.profileRightImg;
                            this.setStandartRight = false;
                        }
                        else { this.setStandartRight = true; }
                         if(response.data.profile[0].text) {
                             $('.profile-text').append(response.data.profile[0].text);
                             this.setText = false;
                         }else {this.setText = true;}
                        this.vk = response.data.profile[0].vk;
                        this.instagram = response.data.profile[0].instagram;
                        this.facebook = response.data.profile[0].facebook;
                        this.twitter = response.data.profile[0].twitter;
                        document.querySelector(".vk-link").setAttribute("href", this.vk);
                        document.querySelector(".instagram-link").setAttribute("href", this.instagram);
                        document.querySelector(".facebook-link").setAttribute("href", this.facebook);
                        document.querySelector(".twitter-link").setAttribute("href", this.twitter);

                    },
                    (error) => {
                        console.log(error.message);
                    }
                )
        },
        cancel: function (cancel) {
            if(cancel.cancel == false) {
                this.change = false;
                this.topImg = null; this.rightImg = null;
                this.tegDownloadTop = "Download";
                this.tegDownloadRight ="Download";
                this.getProfile();
                this.previewFlag = false;
                //document.getElementById("input__file").value = "";
                //document.getElementById("input__file2").value = "";

            }
        },
        preview: function (preview) {
             if(preview.preview == true) {
                 this.previewFlag = true;
                 this.newText = preview.newText;
                 $('.preview-text').append(preview.newText);
                 this.setText = false;
                 this.change = false;
                 console.log(this.rightImg);
                 }
          //  console.log(preview.newText);
        },
         delatedTopImg: function () {
             this.topImg = false;
             this.setStandartTop = true;
         },
         delatedRightImg : function () {
             this.rightImg = false;
             this.setStandartRight = true;
        },
    },
    created(){
        this.getProfile();
    }
}
</script>

<style scoped>

</style>
