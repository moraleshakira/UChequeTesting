<?php $user_roles = isset($user_roles) ? $user_roles : ['Admin', 'Staff']; ?>
<body>
        <div class="sidebar">
          <div class="logo"><img src="./assets/images/logoall-light.png" alt=""></div>
          <ul class="menu">
            <li><a href="s_dash.php"><i class="bx bxs-dashboard"></i><span>staff dashboard</span></a></li>
            <li><a href="s_user.php"><i class="bx bxs-group"></i><span>faculty management</span></a></li>
            <li><a href="s_itl.php"><i class='bx bxs-doughnut-chart'></i><span>faculty ITL</span></a></li>
            <li><a href="s_dtr.php"><i class='bx bxs-time'></i><span>faculty DTR</span></a></li>
            <li><a href="s_overload.php"><i class="bx bxs-user-check"></i><span>Overload overview</span></a></li>
            <li><a href="s_request.php"><i class='bx bxs-message-check'></i><span>Request Overload</span></a></li>
            <li><a href="s_reports.php"><i class='bx bxs-book-alt'></i><span>reports generation</span></a></li>
            <?php if(isset($user_roles) && count($user_roles) > 1) { ?>
            <li class="switch"> <a href="../loginas.php"><i class='bx bx-code'></i><span>switch</span></a></li>
            <?php } ?>
          </ul>
        </div>