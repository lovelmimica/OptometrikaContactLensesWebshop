addLoadEvent( () => {
    let replyBtns = document.querySelectorAll(".comment-reply");
    replyBtns.forEach(replyBtn => {
        replyBtn.addEventListener("click", event => {
            let commentParentId = replyBtn.dataset.id;
            let reviewForm = document.querySelector("#review_form_wrapper");
            reviewForm.remove();
            replyBtn.parentNode.insertBefore(reviewForm, replyBtn);
            let commentParentInput = document.querySelector("#review_form_wrapper #comment_parent");
            commentParentInput.value = commentParentId;
            replyBtn.style.display = "none";
            let activeCancelBtn = document.querySelector(".comment-reply-cancel[style='display: inline-block;']");
            console.log(activeCancelBtn);
            if(activeCancelBtn) activeCancelBtn.style.display = "none";
            replyBtn.parentNode.querySelector(".comment-reply-cancel").style.display = "inline-block";
            let hiddenReplyBtn = document.querySelector(".comment-reply[style='display: none;']");
            if(hiddenReplyBtn) hiddenReplyBtn.style.display = "inline-block";
            console.log("Uredno gotovo");
        });
    });

    let cancelBtns = document.querySelectorAll(".comment-reply-cancel");
    cancelBtns.forEach(cancelBtn => {
        cancelBtn.addEventListener("click", event => {
            let reviewForm = document.querySelector("#review_form_wrapper");
            reviewForm.remove();
            let reviewsWrapper = document.querySelector("#reviews");
            let reviewsWrapperClear = document.querySelector("#reviews .clear");
            reviewsWrapper.insertBefore(reviewForm, reviewsWrapperClear );
            let commentParentInput = document.querySelector("#review_form_wrapper #comment_parent");
            commentParentInput.value = 0;
            cancelBtn.style.display = "none";
            cancelBtn.parentNode.querySelector(".comment-reply").style.display = "inline-block";
        });
    });
});