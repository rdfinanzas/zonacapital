"use strict";

function imageLoad(el, config) {

    this.resize = config.resize || [0, 0]
    this.maxSize = config.maxSize || 1000

    this.onDeleteCB = config.onDeleteCB || null
    this.onViewCB = config.onViewCB || null
    this.onChangeCB = config.onChangeCB || null  // Add new callback

    this.dataUser = config.dataUser || null

    this.el = el;
    this.imageBase64 = "";
    this.mainContainer;
    this.imageDummy

    this.btnUpload;
    this.btnView;
    this.btnDelete;
    this.btnRotate
    this.overLay;
    this.errorContainer
    this.errorText;

    this.imagePreview
    this.imageContPreview
    this.sizeMb = 0;
    this.urlPreview = "";
    this.rotateBase64Image = function (base64Str) {
        const degrees = 90
        const canvas = document.createElement("canvas");
        const ctx = canvas.getContext("2d");
        const image = new Image();
        image.src = base64Str;

        return new Promise(resolve => {

            image.onload = () => {
                canvas.width = degrees % 180 === 0 ? image.width : image.height;
                canvas.height = degrees % 180 === 0 ? image.height : image.width;

                ctx.translate(canvas.width / 2, canvas.height / 2);
                ctx.rotate(degrees * Math.PI / 180);
                ctx.drawImage(image, image.width / -2, image.height / -2);
                resolve(canvas.toDataURL())
            };
            image.onerror = (err) => {
                reject("Error al rotar la iamgen");
            };

        })
    }

    this.resizeImage = function (base64Str, W, H) {
        return new Promise((resolve, reject) => {
            let img = new Image();
            img.src = base64Str;
            img.onload = function () {
                let canvas = document.createElement('canvas');
                let new_width = 0;
                let new_height = 0;
                let width = 0;
                let height = 0;
                let source_width = Number(img.width);
                let source_height = Number(img.height);
                if (img.width >= img.height) {

                    let ratio = source_height / source_width;
                    new_width = W; // assign new width to new resized image
                    new_height = ratio * W;
                } else {

                    let ratio = source_width / source_height;
                    new_width = ratio * W // assign new width to new resized image
                    new_height = H;
                }

                width = new_width
                height = new_height
                canvas.width = width;
                canvas.height = height;

                let ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                resolve(canvas.toDataURL());

            };
            img.onerror = (err) => {
                reject("Error en el escalado de la imagen");
            };

        })
    }
    this.readUploadedFileAsText = function (inputFile) {


        return new Promise((resolve, reject) => {
            if (inputFile === undefined) {
                reject("inputFile is undefined");
                return;
            }



            const reader = new FileReader();

            reader.onerror = (err) => {
                reader.abort();

                reject(err);
            };

            reader.onload = () => {
                if (this.onChangeCB) {
                    this.onChangeCB(reader.result);  // Call the callback when image changes
                }
                resolve(reader.result);
            };
            reader.onprogress = function (data) {
                /*
                if (data.lengthComputable) {
                    let progress = parseInt(
                        (data.loaded / data.total) * 100,
                        10
                    );
                    console.log(data.loaded + " " + data.total);
                }
                */
            };
            reader.readAsDataURL(inputFile.files[0]);
        });
    };

    const toggleElement = function (el) {
        if (el.style.display === 'none') {
            el.style.display = 'block';
        } else {
            el.style.display = 'none';
        }
    }
    const showElement = function (el, display) {
        el.style.display = display;
    }

    const hideElement = function (el, display) {
        el.style.display = 'none';
    }

    this.createElements = function () {
        el.style.display = "none";

        el.setAttribute("accept", "image/*");

        this.mainContainer = document.createElement("div");

        this.mainContainer.classList.add("image_load_cont")

        this.overLay = document.createElement("div");
        this.overLay.classList.add("overlay_image")
        this.overLay.innerHTML = '<i class="fas fa-3x fa-sync-alt fa-spin"></i>'

        this.mainContainer.appendChild(this.overLay)
        hideElement(this.overLay)


        let row = document.createElement("div");
        row.classList.add("row")

        let col = document.createElement("div");
        col.classList.add("col")

        let imgContainer = document.createElement("div");
        imgContainer.classList.add("preview")
        imgContainer.classList.add("text-center")

        this.imageDummy = document.createElement("div");
        this.imageDummy.classList.add("image_empty")
        this.imageDummy.innerHTML = '<svg class="img-thumbnail" width="300" height="300" xmlns="http://www.w3.org/2000/svg" role="img"  preserveAspectRatio="xMidYMid slice" focusable="false"><rect width="100%" height="100%" fill="#868e96"></rect><text x="50%" y="50%"  dominant-baseline="middle" text-anchor="middle" fill="#dee2e6" dy=".3em">IMAGEN</text></svg>'

        imgContainer.appendChild(this.imageDummy)


        this.imagePreview = document.createElement("img");
        this.imagePreview.classList.add("img-thumbnail")
        this.imagePreview.style.maxWidth = "300px"
        this.imagePreview.style.maxHeight = "300px"

        this.imageContPreview = document.createElement("div");
        this.imageContPreview.classList.add("image_preview")

        this.imageContPreview.appendChild(this.imagePreview)
        imgContainer.appendChild(this.imageContPreview)

        hideElement(this.imageContPreview)



        col.appendChild(imgContainer)
        row.appendChild(col)

        this.mainContainer.appendChild(row)


        this.errorContainer = document.createElement("div");
        this.errorContainer.classList.add("text_error_image")

        this.errorText = document.createElement("p");
        this.errorText.classList.add("text-danger")

        this.errorContainer.appendChild(this.errorText)

        this.mainContainer.appendChild(this.errorContainer)
        hideElement(this.errorContainer)




        row = document.createElement("div");
        row.classList.add("row")

        col = document.createElement("div");
        col.classList.add("col")
        col.classList.add("pt-2")
        col.classList.add("text-center")

        this.btnUpload = document.createElement("button");
        this.btnUpload.classList.add("btn")
        this.btnUpload.classList.add("btn-primary")
        this.btnUpload.setAttribute("type", "button");
        this.btnUpload.innerHTML = '<i class="fas fa-upload"></i>'


        this.btnRotate = document.createElement("button");
        this.btnRotate.classList.add("btn")
        this.btnRotate.classList.add("btn-info")
        this.btnRotate.setAttribute("type", "button");
        this.btnRotate.innerHTML = '<i class="fas fa-sync-alt"></i>'


        this.btnView = document.createElement("button");
        this.btnView.classList.add("btn")
        this.btnView.classList.add("btn-success")
        this.btnView.setAttribute("type", "button");
        this.btnView.innerHTML = '<i class="fas fa-eye"></i>'

        this.btnDelete = document.createElement("button");
        this.btnDelete.classList.add("btn")
        this.btnDelete.classList.add("btn-danger")
        this.btnDelete.setAttribute("type", "button");
        this.btnDelete.innerHTML = '<i class="fas fa-trash"></i>'

        hideElement(this.btnView)
        hideElement(this.btnDelete)
        hideElement(this.btnRotate)
        col.appendChild(this.btnUpload)
        col.appendChild(this.btnRotate)

        col.appendChild(this.btnView)
        col.appendChild(this.btnDelete)

        row.appendChild(col)
        this.mainContainer.appendChild(row)

        this.el.parentElement.appendChild(this.mainContainer)



        this.btnUpload.addEventListener("click", this.onClickBtnUpload.bind(this));
        this.btnRotate.addEventListener("click", this.onClickBtnRotate.bind(this));
        this.btnDelete.addEventListener("click", this.onClickBtnDelete.bind(this));
        this.btnView.addEventListener("click", this.onClickBtnView.bind(this));



        this.imageDummy.addEventListener("click", this.onClickBtnUpload.bind(this));

    };


    this.onClickBtnRotate = async function (evt) {
        try {
            showElement(this.overLay, 'flex')
            const imageBase64 = await this.rotateBase64Image(this.imageBase64)
            this.setBase64Img(imageBase64)

            hideElement(this.overLay)
        } catch (err) {
            hideElement(this.overLay)

            this.handleError(err);
        }


    }
    this.onClickBtnUpload = function (evt) {

        this.el.click();
    }

    this.onClickBtnDelete = function (evt) {

        this.deleteImg()
    }

    this.onClickBtnView = function (evt) {

        this.onViewCB && this.onViewCB(this.imagePreview.src)

    }




    this.handleError = function (err) {

        showElement(this.errorContainer, 'block')
        this.errorText.innerHTML = err

        console.error(err);
    };
    this.getBase64Img = function () {
        return this.imageBase64;
    }


    this.setBase64Img = function (base64) {
        this.imageBase64 = base64
        this.imagePreview.src = base64
        showElement(this.btnDelete, 'inline-block')
        showElement(this.btnRotate, 'inline-block')
    }

    this.setImgPreviewUrl = function (url) {
        this.imageBase64 = ""
        this.imagePreview.src = url
        this.urlPreview = url
        showElement(this.btnDelete, 'inline-block')
        // showElement(this.btnRotate,'inline-block')

        showElement(this.imageContPreview, 'block')

        showElement(this.btnView, 'inline-block')
        hideElement(this.imageDummy)

        hideElement(this.overLay)

        hideElement(this.errorContainer)
    }

    this.getBase64Img = function (base64) {
        return this.imageBase64;
    }
    this.getUrlPreview = function () {
        return this.urlPreview;
    }



    this.deleteImg = function () {
        this.imageBase64 = ""
        this.imagePreview.src = ""
        this.urlPreview = ""

        hideElement(this.btnDelete)
        hideElement(this.btnRotate)

        showElement(this.imageDummy, 'block')
        hideElement(this.imageContPreview)
        this.onDeleteCB && this.onDeleteCB(this.dataUser)

    }
    this.getDataUser = function () {
        return this.dataUser

    }




    this.onChangeImage = async function (evt) {
        
        try {
            showElement(this.overLay, 'flex')
            this.imageBase64 = await this.readUploadedFileAsText(evt.target);
            if (this.resize[0] !== 0 && this.resize[1] !== 0) {
                this.imageBase64 = await this.resizeImage(this.imageBase64, this.resize[0], this.resize[1]);

            }
            const sizeInBytes = (this.imageBase64.length) * (3 / 4) - 2;
            this.sizeMb = sizeInBytes / 1000000
            if (this.sizeMb > this.maxSize) {
                this.handleError("La imagen no debe superar los " + this.maxSize + " MB.");
                hideElement(this.overLay)
                return;
            }


            this.setBase64Img(this.imageBase64)


            showElement(this.imageContPreview, 'block')
            hideElement(this.imageDummy)

            hideElement(this.overLay)


            hideElement(this.errorContainer)
        } catch (err) {
            hideElement(this.overLay)

            this.handleError(err);
        }

        //  console.log(this.imageBase64);
    };

    this.createElements();

    this.el.addEventListener("change", this.onChangeImage.bind(this));

}