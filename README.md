# Use-It - Features, Abilities, Usages and Consumptions

Feature can be either quantity type or ability type.

When feature has been granted to a model ( user or team or someone else ), lets called creator, usage will be generated
for the creator.
This scenario is aimed for such situation that there is a team and when a team is subscribed to the feature, all team
members can consume the feature created by the team.

When usage has the same feature id and creator id, higher level usage will be consumed first.

## Testing

`composer run test`
