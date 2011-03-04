#include <iostream>

#include "chash.h"

using namespace std;

int main(int argc,char *argv[])
{
	ConsistHash ch;
	cout<<" please input servername quit to exit."<<endl;
	string servername;
	for(;;)
	{
		cout<<"servername:"<<endl;
		cin>>servername;
		cout<<"you input server name:"<<servername<<endl;
		if("quit" == servername ) break;
		ch.add_node(servername);
	}
	for(list<pair<unsigned int,string> >::iterator it=ch.circle.begin();it!=ch.circle.end();it++)
	{
		cout<<it->first<<"["<<it->second<<"]"<<endl;
	}
	string key;
	for(;;)
	{
		cout<<"key:"<<endl;
		cin>>key;
		if("quit" == key ) break;
		cout<<"[key]"<<key<<"[value]"<<ch.get_value(key)<<"[server]"<<ch.get_node(key)<<endl;
	}
	return 0;
}
