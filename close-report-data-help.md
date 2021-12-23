* The company maybe have multiple opportunities statuses and the same opportunity maybe exist multiple times for the same company.

* No Demo set: mean the company has "FIRST_RESPONSE_SENTIMENT" (First Response Sentiment) and the "FIRST_RESPONSE_SENTIMENT" is "Positive" and didn't opportunity status.

// Custom fields (Filters)
* The 'Campaign', 'Job Title Responded', 'Company Size' and 'Lead Owner' a text lead custom fields but it will be displayed as dropdown list.

* The Date filter will be created static from code. The start year can be configured from configuration file to the current year.


// Lead generation metric report:
* Header Total companies Approached: is the sum of 'total_results' for all channels(that have FIRST_RESPONSE_MEDIUM).
* Header total responses: is the sum of total respose for all channel (for all companies that have a 'First Response Sentiment' and '# of Written Outreach')
* Header Total Company Response Rate: Header total responses/Header Total companies.
* Header Total Company Positive Response Rate:  (is the sum of 'first response sentiment: positive' for all channels / Header total responses.
* Header Positive Response Rate Out Of Total companies:  (is the sum of 'first response sentiment: positive' for all channels / Header total companies.
* The lead should have 'First Response Sentiment', '# of Written Outreach' and 'First Response Medium' to be consider in this report (to be used in the data calculation).
* The lead have a reponse if ('First Response Sentiment' and '# of Written Outreach') is exist.
* Percent of Responses %: is the total responses for channel / by the total number of companies for the channel.
* Positive: is the total posistive response(first response sentiment: positive).
* Positive Response %: is the total posistive response(first response sentiment: positive) / total responses for company
* Negative Response %: is the total negative response(first response sentiment: negative) / total responses for company
