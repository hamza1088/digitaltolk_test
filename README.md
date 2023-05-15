# digitaltolk_test

# what makes it terrible code / changes/refactoring

# BookingController
1) DTApi\Http\Requests is unused throghout the fail (according to my IDE), so it should be removed from the top.
2) I think the types mentioned as PHP docblock should be changed to typed properties like protected BookingRepository $repository;.
3) $repository should be changed from protected to private.
4) If there are chances of using multiple repositories in the controller, $repository should be changed to $bookingRepository at the time of declaration and everywhere in the controller as well.
5) As response() function is undefined, I believe we should use the Illuminate\Http\Response and then response()->json($response);
    But I think response() is a helper function available in the helper.php So it's fine. (Not changing this is the code due to uncertainty).
6) The return type of all functions in Controller is mixed. I think it should be changed to the return type of that response() fucntion. Like Illuminate\Http\Response
or ?Illuminate\Http\Response (where the possibility is null)
7) One problem I'm seeing is that if both conditions in the index() are not true then variable $response will be undefined in the end (response($response);)
8) Type of param $id is not mentioned in the show()
9) Use of exception handling and findOrFail() instead of find()
10) The array_except() function should replaced with the except() method provided by Laravel's Request object.
11) Code refactoring
12) Formatted with php cs fixer and php intelephense.

# BookingRepository
1) Add type to properties
2) $user_id type is not mentioned
3) Wrong/inappropriate variabke names like cuser and noramlJobs etc
4) Removed unnecessary checks for $cuser before accessing its properties.
5) Refactoring.. e.g remove duplicate checks and use ternary operations instead of if/else
6) Formatted with php cs fixer and php intelephense.


# what makes it good code
Laravel structure/architecture is being followed.
Eager loading.

# Thoughts

Excessive Use of if/else
Ternary operators must be used while checking variable is set/null etc
Overall, the code is good. It has minor issues with formatting according to the php intelephense.
Also I don't understand why there's a helper file inside of test  folder ? I think it's not right.
Also, I would create another directory for job related methods e.g acceptJob(), endJob() etc.




