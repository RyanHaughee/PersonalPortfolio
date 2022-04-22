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
    body{
        background:#000000;
        color:#FFFFFF;
    }
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
    <body style="background:#000000">
        <div class="container">
            <div class="row">
                <h1>Ryan Haughee</h1>
            </div>
            <div class="row">
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
    </body>
</html>
 