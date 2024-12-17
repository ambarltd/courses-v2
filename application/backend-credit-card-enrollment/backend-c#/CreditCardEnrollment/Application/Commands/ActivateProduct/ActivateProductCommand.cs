using MediatR;

namespace CreditCardEnrollment.Application.Commands.ActivateProduct;

public record ActivateProductCommand(string ProductId) : IRequest<Unit>;
