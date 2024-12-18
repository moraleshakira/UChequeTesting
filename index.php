<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="./assets/login.css">
    <title>Login</title>
</head>
<body>
  <div class="wrapper">
    <div class="container main">
      <div class="row">
          <div class="col-md-6 side-image">
                      
              <!-------------      image     ------------->
              
              <img src="" alt="">
          </div>

          <div class="col-md-6 right">
              
              <div class="input-box">
                <form action="./controller/login_process.php" method="POST"> 
                  <img src="./assets/images/logoall-grey.png" class="logo-font" alt="">
                  <header>Log In</header>
                  <div class="input-field">
                    <input name="emailAddress" type="text" class="input" id="email" required="" autocomplete="off">
                    <label for="email">Email</label> 
                  </div> 
                  <div class="input-field">
                    <input name="password" type="password" class="input" id="pass" required="">
                    <label for="pass">Password</label>
                  </div> 
                  <div class="input-field">
                    <button type="submit" class="submit"  name="login">Login</button>
                  </div> 
                </form>
                <div class="signin">
                <span>Already have an account? <a href="#">Log in here</a></span>
                </div>
              </div>  
          </div>
      </div>
    </div>
</div>
</body>
</html>