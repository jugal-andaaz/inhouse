<div class="card shadow-sm custom-card half-width">
    <div class="card-body card-body-table">
        <h6>Add Special Instruction</h6>
        <h5>No records has been added.</h5>
        <form class="flex" action="" method="post">
            <table class="table table-bordered table-striped ct-edit-table">
                <thead>
                    <tr>
                        <th class="ct-instra-th" scope="col">
                            <div class="head">
                                <label for="heading">Heading</label>
                                <input type="text" id="heading" name="heading" class="form-control mb-2" placeholder="Enter Heading">
                            </div>
                        </th>
                        <th  class="ct-instra-th" scope="col">
                            <div class="instra"><label for="instruction">Instruction</label>
                                <input type="text" id="instruction" name="instruction" class="form-control mb-2" placeholder="Enter Instruction">   
                            </div>
                        </th>
                        <th  class="ct-instra-th" scope="col">
                            <div class="button-instra">
                                <button type="submit" class="btn btn-primary button-instra w-100">Add</button>
                            </div>
                        </th>
                    </tr>
                </thead>
            </table>
        </form>

        <table class="table table-bordered table-striped ct-edit-table">
            <thead>
                <tr>
                    <th class="ct-edit-th" scope="col">Heading</th>
                    <th  class="ct-edit-th" scope="col">Instruction</th>
                    <th  class="ct-edit-th" scope="col">-</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td  class="ct-edit-td" >Test</td>
                    <td class="ct-edit-td" > Test</td>
                    <td class="ct-edit-td" >
                        <a href="#">Edit</a>
                        <a href="#">Delete</a>
                    </td>
                </tr>
                <tr>
                    <td  class="ct-edit-td" >Test</td>
                    <td class="ct-edit-td" > Test</td>
                    <td class="ct-edit-td" >
                        <a href="#">Edit</a>
                        <a href="#">Delete</a>
                    </td>
                </tr>
            </tbody>
        </table>
        <button type="submit" class="btn btn-primary btn-sm w-100 ">Save</button>
    </div>
</div>