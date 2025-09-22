# Logical Data Model
The data consists of *nodes*.

## Nodes
There are two types of *nodes*, *person* nodes and *organization* nodes.
Each node has an *description*.

**Example**: There might be a *person* node with the description "Bugs Bunny" and there might be an *organization* node with description "Looney Tunes".

## Connections
There exist *connections* between nodes. A *connection* has a description and is directed, that is, it has a a source node and a target node.

**Example**: There might be a *connection* from node with description "Bugs Bunny" to node with description "Looney Tunes". The description has the description "joins".
This reads as Bugs Bunny joined Looney Tunes".

## ID
nodes and connections each have an ID.
The ID of nodes and/or connections must be unique.

