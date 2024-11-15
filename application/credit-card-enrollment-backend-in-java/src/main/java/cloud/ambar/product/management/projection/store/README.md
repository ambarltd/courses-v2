Projections are where we take events and build useful state from them, but we will still need somewhere to store that
state to be able to use it elsewhere in our application (queries, queries for command validations, etc). We also want to make
sure that our projection will not collide with projections used elsewhere in the application.

For this we leverage mongodb for its ability to scale horizontally very well to handle high read throughput on our 
derived state (projections). We can also leverage mongo collections to be able to make sure each component of our service
has its own dedicated place within mongodb and prevent any id reuse from causing collisions across projections 
(or with new projection versions!)