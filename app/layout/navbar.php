<div class="top-navbar ">
    <div class="xp-topbar">
        <div class="row align-items-center">
            <div class="col-2 col-md-1 col-lg-1 order-2 order-md-1 align-self-center">
                <div class="xp-menubar">
                    <span class="material-icons text-white">signal_cellular_alt</span>
                </div>
            </div>
            <div class="col-md-5 col-lg-3 order-3 order-md-2">
                <?php
                $hideSearchPages = ['Dashboard', 'View Dashboard', 'Service Report' ];
                if (!in_array($pageTitle, $hideSearchPages)):
                ?>
                    <div class="xp-searchbar">
                        <form>
                            <div class="input-group">
                                <input type="search" class="form-control" id="searchInput" placeholder="Search">
                                <div class="input-group-append">
                                    <button class="btn" type="submit" id="button-addon2">
                                        <svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#e3e3e3">
                                            <path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-5 col-lg-7 order-1 order-md-3">
                <div class="xp-breadcrumbbar d-flex justify-content-left align-items-center ml-5">
                    <h4 class="page-title m-0 text-white"><?php echo isset($pageTitle) ? $pageTitle : '' ?></h4>
                </div>
            </div>
            <div class="col-md-2 col-lg-1 order-4 order-md-4">
                <div class="xp-userbar d-flex justify-content-end align-items-center">
                    <div class="d-flex align-items-center text-white">
                        <span class="material-icons mr-2">account_circle</span>
                        <span class="username"><?php echo isset($_SESSION['username']) ? $_SESSION['username'] : 'Admin'; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>