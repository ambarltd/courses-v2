using MediatR;

namespace CreditCardEnrollment.Application.Commands.DeactivateProduct;

public record DeactivateProductCommand(string ProductId) : IRequest<Unit>;
