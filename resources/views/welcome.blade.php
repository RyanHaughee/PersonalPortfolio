<!DOCTYPE html>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.7/dist/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.3.1/dist/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
<script src="https://kit.fontawesome.com/e45fe32dae.js" crossorigin="anonymous"></script>

<style>
    /* body{
        background:#FFFFFF;
        color:#000000;
    } */
    .card{
        color:#000000;
    }
    .card:hover{
        background:#DDDDDD;
        cursor:pointer;
    }
</style>
<script>

    window.addEventListener('DOMContentLoaded', (event) => {
        var draft_card = document.querySelector("#draft");
        draft_card.addEventListener("click",function(){
            window.location = "/draft";
        })

        var tournament_card = document.querySelector("#tournament");
        tournament_card.addEventListener("click",function(){
            window.location = "/scheduler";
        })
    });

</script>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <body>
        <div class="row">
            <div class="col-sm-2" style="background-color:#E18B69; height:100vh;">
                <div style="color:#FFFFFF; padding:10px;">Here is some text about how great I am!</div>
            </div>
            <div class="col-sm-10" style="max-height: 100vh; overflow-y: scroll;">
                <div class="container">
                    <div style="height:100vh;display: flex; justify-content: center; align-content: center; flex-direction: column;">
                        <div style="font-size:60px">
                            <strong>RYAN <span style="color:#E18B69">HAUGHEE</span></strong>
                        </div>
                        <div style="font-size:30px; color:#7C7C7C">
                            Web Application Developer
                        </div>
                        <div style="font-size:20px; color:#7C7C7C;margin-top:10px;">
                            Wilmington, NC <span>&#183;</span> <a href="mailto:ryan.haughee@gmail.com">ryan.haughee@gmail.com</a>
                        </div>
                        <div style="margin-top:20px;font-size:20px; color:#7C7C7C">
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/ca/LinkedIn_logo_initials.png/768px-LinkedIn_logo_initials.png" style="width:auto; height:40px"/> <span>&#183;</span> 
                            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/9/91/Octicons-mark-github.svg/2048px-Octicons-mark-github.svg.png" style="width:auto; height:40px"/>
                        </div>
                    </div>
                    <div style="font-size:40px">
                        <strong>ABOUT <span style="color:#E18B69">ME</span></strong>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                        </div>
                    </div>

                    <div style="font-size:40px; margin-top:200px">
                        <strong>WORK <span style="color:#E18B69">EXPERIENCE</span></strong>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <img style="max-height:120px; width:auto" src="https://images.squarespace-cdn.com/content/v1/5c70a6df840b165c67d063d5/9944ada0-de10-4aeb-a7da-8598cc98fe3b/KWIPPED+APPROVE+Logo.jpg"/>
                            <div style="font-size:20px; color:#7C7C7C">
                                <strong><u>KWIPPED / APPROVE</u></strong>
                            </div>
                            <div style="margin-left:20px; color:#7C7C7C">
                                <strong>Junior Developer</strong> (August 1st, 2019 - August 1st, 2020)
                            </div>
                        </div>
                    </div>
                    <div style="font-size:40px; margin-top:100px">
                        <strong>PERSONAL <span style="color:#E18B69">PROJECTS</span></strong>
                    </div>
                    <div class="row" style="height:4000px;">
                        <div class="col-sm-4">
                            <div id="draft" class="card" style="width: 100%; margin: 10px">
                                <img class="card-img-top" src="https://i.ibb.co/dL5q3ct/Screen-Shot-2022-04-11-at-3-58-06-PM.png" alt="Card image cap">
                                <div class="card-body">
                                    <h5 class="card-title">Fantasy Football Draft App</h5>
                                    <p class="card-text">Customizable fantasy football draft room. Capable of hosting both mock and live drafts.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div id="tournament" class="card" style="width: 100%; margin: 10px">
                                <img class="card-img-top" src="https://i.ibb.co/dL5q3ct/Screen-Shot-2022-04-11-at-3-58-06-PM.png" alt="Card image cap">
                                <div class="card-body">
                                    <h5 class="card-title">Tournament Scheduler</h5>
                                    <p class="card-text">Customizable fantasy football draft room. Capable of hosting both mock and live drafts.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div id="draft" class="card" style="width: 100%; margin: 10px">
                                <img class="card-img-top" src="https://i.ibb.co/dL5q3ct/Screen-Shot-2022-04-11-at-3-58-06-PM.png" alt="Card image cap">
                                <div class="card-body">
                                    <h5 class="card-title">Fantasy Football Draft App</h5>
                                    <p class="card-text">Customizable fantasy football draft room. Capable of hosting both mock and live drafts.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
 